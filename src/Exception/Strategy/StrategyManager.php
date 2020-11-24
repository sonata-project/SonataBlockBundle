<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Exception\Strategy;

use Sonata\BlockBundle\Exception\Filter\FilterInterface;
use Sonata\BlockBundle\Exception\Renderer\RendererInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The strategy manager handles exceptions thrown by a block. It uses an exception filter to identify which exceptions
 * it should handle or ignore. It then uses an exception renderer to "somehow" display the exception.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class StrategyManager implements StrategyManagerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $renderers;

    /**
     * @var array
     */
    private $blockFilters;

    /**
     * @var array
     */
    private $blockRenderers;

    /**
     * @var string
     */
    private $defaultFilter;

    /**
     * @var string
     */
    private $defaultRenderer;

    /**
     * @param ContainerInterface $container      Dependency injection container
     * @param array              $filters        Filter definitions
     * @param array              $renderers      Renderer definitions
     * @param array              $blockFilters   Filter names for each block
     * @param array              $blockRenderers Renderer names for each block
     */
    public function __construct(ContainerInterface $container, array $filters, array $renderers, array $blockFilters, array $blockRenderers)
    {
        $this->container = $container;
        $this->filters = $filters;
        $this->renderers = $renderers;
        $this->blockFilters = $blockFilters;
        $this->blockRenderers = $blockRenderers;
    }

    /**
     * Sets the default filter name.
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultFilter(string $name): void
    {
        if (!\array_key_exists($name, $this->filters)) {
            throw new \InvalidArgumentException(sprintf('Cannot set default exception filter "%s". It does not exist.', $name));
        }

        $this->defaultFilter = $name;
    }

    /**
     * Sets the default renderer name.
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultRenderer(string $name): void
    {
        if (!\array_key_exists($name, $this->renderers)) {
            throw new \InvalidArgumentException(sprintf('Cannot set default exception renderer "%s". It does not exist.', $name));
        }

        $this->defaultRenderer = $name;
    }

    public function handleException(\Throwable $exception, BlockInterface $block, ?Response $response = null): Response
    {
        $response = $response ?: new Response();
        $response->setPrivate();

        $filter = $this->getBlockFilter($block);
        if ($filter->handle($exception, $block)) {
            $renderer = $this->getBlockRenderer($block);

            // Convert throwable to exception
            if (!$exception instanceof \Exception) {
                $exception = new \Exception($exception->getMessage(), (int) $exception->getCode(), $exception);
            }

            $response = $renderer->render($exception, $block, $response);
        }
        // render empty block template?

        return $response;
    }

    /**
     * Returns the exception renderer for given block.
     *
     * @throws \RuntimeException|\InvalidArgumentException
     */
    public function getBlockRenderer(BlockInterface $block): RendererInterface
    {
        $type = $block->getType();
        $name = $this->blockRenderers[$type] ?? $this->defaultRenderer;

        return $this->getRendererService($name);
    }

    /**
     * Returns the exception filter for given block.
     *
     * @throws \RuntimeException|\InvalidArgumentException
     */
    public function getBlockFilter(BlockInterface $block): FilterInterface
    {
        $type = $block->getType();
        $name = $this->blockFilters[$type] ?? $this->defaultFilter;

        return $this->getFilterService($name);
    }

    /**
     * Returns the filter service for given filter name.
     *
     * @throws \RuntimeException|\InvalidArgumentException
     */
    private function getFilterService(string $name): FilterInterface
    {
        if (!isset($this->filters[$name])) {
            throw new \RuntimeException('The filter "%s" does not exist.');
        }

        $service = $this->container->get($this->filters[$name]);

        if (!$service instanceof FilterInterface) {
            throw new \InvalidArgumentException(sprintf('The service "%s" is not an exception filter.', $name));
        }

        return $service;
    }

    /**
     * Returns the renderer service for given renderer name.
     *
     * @throws \RuntimeException|\InvalidArgumentException
     */
    private function getRendererService(string $name): RendererInterface
    {
        if (!isset($this->renderers[$name])) {
            throw new \RuntimeException('The renderer "%s" does not exist.');
        }

        $service = $this->container->get($this->renderers[$name]);

        if (!$service instanceof RendererInterface) {
            throw new \InvalidArgumentException(sprintf('The service "%s" is not an exception renderer.', $name));
        }

        return $service;
    }
}
