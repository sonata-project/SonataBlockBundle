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

namespace Sonata\BlockBundle\Block;

use Psr\Container\ContainerInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\ContainerInterface as DependencyInjectionContainerInterface;

final class BlockServiceManager implements BlockServiceManagerInterface
{
    /**
     * @var array<string, string|BlockServiceInterface>
     */
    private $services;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    private $inValidate = false;

    /**
     * @var array<string, string[]>
     */
    private $contexts;

    /**
     * @var string[]
     */
    private $containerTypes;

    /**
     * NEXT_MAJOR: make $containerTypes not nullable.
     *
     * @param string[]|null $containerTypes
     */
    public function __construct(ContainerInterface $container, ?array $containerTypes = null)
    {
        $this->services = [];
        $this->contexts = [];
        $this->container = $container;

        if (null === $containerTypes) {
            @trigger_error(
                sprintf(
                    'Not Passing an array as argument 2 for method "%s" is deprecated since sonata-project/block-bundle 4.x. The argument will be required in 5.0.',
                    __METHOD__
                ),
                \E_USER_DEPRECATED
            );

            if ($container instanceof DependencyInjectionContainerInterface) {
                /** @var string[] $containerTypes */
                $containerTypes = $container->getParameter('sonata.block.container.types');
            } else {
                throw new \LogicException(
                    sprintf(
                        "Argument 1 for method '%s' needs to be an instance of '%s' in case the containerTypes are not passed as the second argument.",
                        __METHOD__,
                        DependencyInjectionContainerInterface::class
                    )
                );
            }
        }

        $this->containerTypes = $containerTypes;
    }

    public function get(BlockInterface $block): BlockServiceInterface
    {
        $blockType = $block->getType();
        if (null === $blockType) {
            throw new \RuntimeException('The block service `` does not exist');
        }

        $this->load($blockType);

        $service = $this->services[$blockType];
        \assert($service instanceof BlockServiceInterface);

        return $service;
    }

    public function getService(string $name): BlockServiceInterface
    {
        return $this->load($name);
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * @param BlockServiceInterface|string $service
     */
    public function add(string $name, $service, array $contexts = []): void
    {
        if (!\is_string($service) && !$service instanceof BlockServiceInterface) {
            throw new \TypeError(sprintf(
                'Argument 2 passed to %s() must be of type string or an object implementing %s, %s given',
                __METHOD__,
                BlockServiceInterface::class,
                \is_object($service) ? \get_class($service) : \gettype($service)
            ));
        }
        $this->services[$name] = $service;

        foreach ($contexts as $context) {
            if (!\array_key_exists($context, $this->contexts)) {
                $this->contexts[$context] = [];
            }

            $this->contexts[$context][] = $name;
        }
    }

    public function getServices(): array
    {
        foreach ($this->services as $id) {
            if (\is_string($id)) {
                $this->load($id);
            }
        }

        /** @var array<string, BlockServiceInterface> $services */
        $services = $this->services;

        return $services;
    }

    public function getServicesByContext(string $context, bool $includeContainers = true): array
    {
        if (!\array_key_exists($context, $this->contexts)) {
            return [];
        }

        $services = [];

        foreach ($this->contexts[$context] as $name) {
            if (!$includeContainers && \in_array($name, $this->containerTypes, true)) {
                continue;
            }

            $services[$name] = $this->getService($name);
        }

        return $services;
    }

    /**
     * @todo: this function should be remove into a proper statefull object
     */
    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        if (null === $block->getId() && null === $block->getType()) {
            return;
        }

        if ($this->inValidate) {
            return;
        }

        // As block can be nested, we only need to validate the main block, not the children
        try {
            $this->inValidate = true;

            $blockService = $this->get($block);

            if ($blockService instanceof EditableBlockService) {
                $blockService->validate($errorElement, $block);
            }

            $this->inValidate = false;
        } catch (\Exception $e) {
            $this->inValidate = false;
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function load(string $type): BlockServiceInterface
    {
        if (!$this->has($type)) {
            throw new \RuntimeException(sprintf('The block service `%s` does not exist', $type));
        }

        if (!$this->services[$type] instanceof BlockServiceInterface) {
            $blockService = $this->container->get($type);
            if (!$blockService instanceof BlockServiceInterface) {
                throw new \RuntimeException(sprintf('The service %s does not implement BlockServiceInterface', $type));
            }

            $this->services[$type] = $blockService;
        }

        return $this->services[$type];
    }
}
