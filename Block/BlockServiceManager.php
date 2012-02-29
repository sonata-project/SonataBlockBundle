<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;

use Sonata\AdminBundle\Validator\ErrorElement;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockServiceManager implements BlockServiceManagerInterface
{
    protected $blockServices;

    protected $container;

    protected $contexts;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param $debug
     * @param null|\Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, $debug, LoggerInterface $logger = null, array $contexts = array())
    {
        $this->blockServices = array();
        $this->container     = $container;
        $this->contexts      = $contexts;
    }

    /**
     * @throws \RuntimeException
     * @param $type
     * @return \Sonata\BlockBundle\Block\BlockServiceInterface
     */
    private function loadService($type)
    {
        if (!$this->hasBlockService($type)) {
            throw new \RuntimeException(sprintf('The block service `%s` does not exists', $type));
        }

        if (!$this->blockServices[$type] instanceof BlockServiceInterface) {
            $this->blockServices[$type] = $this->container->get($type);
        }

        if (!$this->blockServices[$type] instanceof BlockServiceInterface) {
            throw new \RuntimeException(sprintf('The service %s does not implement BlockServiceInterface', $type));
        }

        return $this->blockServices[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockService(BlockInterface $block)
    {
        $this->loadService($block->getType());

        return $this->blockServices[$block->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function hasBlockService($id)
    {
        return isset($this->blockServices[$id]) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function addBlockService($name, $service)
    {
        $this->blockServices[$name] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function setBlockServices(array $blockServices)
    {
        $this->blockServices = $blockServices;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockServices()
    {
        foreach ($this->blockServices as $name => $id) {
            if (is_string($id)) {
                $this->loadService($id);
            }
        }

        return $this->blockServices;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadedBlockServices()
    {
        $services = array();

        foreach ($this->blockServices as $service) {
            if (!$service instanceof BlockServiceInterface) {
                continue;
            }

            $services[] = $service;
        }

        return $services;
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        if (!$block->getId() && !$block->getType()) {
            return;
        }

        $service = $this->getBlockService($block);
        $service->validateBlock($errorElement, $block);
    }
}