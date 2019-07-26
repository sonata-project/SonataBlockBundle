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

use Psr\Log\LoggerInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class BlockServiceManager implements BlockServiceManagerInterface
{
    /**
     * @var array
     */
    private $services;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var bool
     */
    private $inValidate;

    /**
     * @var array
     */
    private $contexts;

    /**
     * @param mixed $debug
     */
    public function __construct(ContainerInterface $container, $debug, ?LoggerInterface $logger = null)
    {
        $this->services = [];
        $this->contexts = [];
        $this->container = $container;
    }

    public function get(BlockInterface $block): BlockServiceInterface
    {
        $this->load($block->getType());

        return $this->services[$block->getType()];
    }

    public function getService($id): BlockServiceInterface
    {
        return $this->load($id);
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
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
        foreach ($this->services as $name => $id) {
            if (\is_string($id)) {
                $this->load($id);
            }
        }

        return $this->sortServices($this->services);
    }

    public function getServicesByContext(string $context, bool $includeContainers = true): array
    {
        if (!\array_key_exists($context, $this->contexts)) {
            return [];
        }

        $services = [];

        $containers = $this->container->getParameter('sonata.block.container.types');

        foreach ($this->contexts[$context] as $name) {
            if (!$includeContainers && \in_array($name, $containers, true)) {
                continue;
            }

            $services[$name] = $this->getService($name);
        }

        return $this->sortServices($services);
    }

    /**
     * @todo: this function should be remove into a proper statefull object
     *
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        if (!$block->getId() && !$block->getType()) {
            return;
        }

        if ($this->inValidate) {
            return;
        }

        // As block can be nested, we only need to validate the main block, no the children
        try {
            $this->inValidate = true;
            $this->get($block)->validateBlock($errorElement, $block);
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
            $this->services[$type] = $this->container->get($type);
        }

        if (!$this->services[$type] instanceof BlockServiceInterface) {
            throw new \RuntimeException(sprintf('The service %s does not implement BlockServiceInterface', $type));
        }

        return $this->services[$type];
    }

    /**
     * Sort alphabetically services.
     *
     * @param BlockServiceInterface[] $services
     */
    private function sortServices(array $services): array
    {
        uasort($services, static function (BlockServiceInterface $a, BlockServiceInterface $b): int {
            if ($a->getName() === $b->getName()) {
                return 0;
            }

            return ($a->getName() < $b->getName()) ? -1 : 1;
        });

        return $services;
    }
}
