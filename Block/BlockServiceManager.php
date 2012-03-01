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
    protected $services;

    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param $debug
     * @param null|\Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, $debug, LoggerInterface $logger = null)
    {
        $this->services  = array();
        $this->container = $container;
    }

    /**
     * @throws \RuntimeException
     * @param $type
     * @return \Sonata\BlockBundle\Block\BlockServiceInterface
     */
    private function load($type)
    {
        if (!$this->has($type)) {
            throw new \RuntimeException(sprintf('The block service `%s` does not exists', $type));
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
     * {@inheritdoc}
     */
    public function get(BlockInterface $block)
    {
        $this->load($block->getType());

        return $this->services[$block->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function getService($id)
    {
        return $this->load($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->services[$id]) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function add($name, $service)
    {
        $this->services[$name] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function setServices(array $blockServices)
    {
        $this->services = $blockServices;
    }

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        foreach ($this->services as $name => $id) {
            if (is_string($id)) {
                $this->load($id);
            }
        }

        return $this->services;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadedServices()
    {
        $services = array();

        foreach ($this->services as $service) {
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
    public function validate(ErrorElement $errorElement, BlockInterface $block)
    {
        if (!$block->getId() && !$block->getType()) {
            return;
        }

        $this->get($block)->validateBlock($errorElement, $block);
    }
}