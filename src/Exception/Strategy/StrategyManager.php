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
    private ?string $defaultFilter = null;

    private ?string $defaultRenderer = null;

    /**
     * @psalm-suppress ContainerDependency
     *
     * @param ContainerInterface    $container      Dependency injection container
     * @param array<string, string> $filters        Filter definitions
     * @param array<string, string> $renderers      Renderer definitions
     * @param array<string, string> $blockFilters   Filter names for each block
     * @param array<string, string> $blockRenderers Renderer names for each block
     */
    public function __construct(
        private ContainerInterface $container,
        private array $filters,
        private array $renderers,
        private array $blockFilters,
        private array $blockRenderers,
    ) {
    }

    /**
     * Sets the default filter name.
     *
     * @throws \InvalidArgumentException
     */
    public function setDefaultFilter(string $name): void
    {
        if (!\array_key_exists($name, $this->filters)) {
            throw new \InvalidArgumentException(\sprintf('Cannot set default exception filter "%s". It does not exist.', $name));
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
            throw new \InvalidArgumentException(\sprintf('Cannot set default exception renderer "%s". It does not exist.', $name));
        }

        $this->defaultRenderer = $name;
    }

    public function handleException(\Throwable $exception, BlockInterface $block, ?Response $response = null): Response
    {
        $response ??= new Response();
        $response->setPrivate();

        $filter = $this->getBlockFilter($block);
        if ($filter->handle($exception, $block)) {
            $renderer = $this->getBlockRenderer($block);

            // Convert throwable to exception
            if (!$exception instanceof \Exception) {
                /** @psalm-suppress PossiblyInvalidArgument */
                $exception = new \Exception($exception->getMessage(), $exception->getCode(), $exception);
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
        if (null === $name) {
            throw new \RuntimeException('No default renderer was set.');
        }

        if (!isset($this->renderers[$name])) {
            throw new \RuntimeException('The renderer "%s" does not exist.');
        }

        $service = $this->container->get($this->renderers[$name]);

        if (!$service instanceof RendererInterface) {
            throw new \InvalidArgumentException(\sprintf('The service "%s" is not an exception renderer.', $name));
        }

        return $service;
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
        if (null === $name) {
            throw new \RuntimeException('No default filter was set.');
        }

        if (!isset($this->filters[$name])) {
            throw new \RuntimeException('The filter "%s" does not exist.');
        }

        $service = $this->container->get($this->filters[$name]);

        if (!$service instanceof FilterInterface) {
            throw new \InvalidArgumentException(\sprintf('The service "%s" is not an exception filter.', $name));
        }

        return $service;
    }
}
