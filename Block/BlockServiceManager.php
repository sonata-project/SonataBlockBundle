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
use Sonata\BlockBundle\Block\BlockManagerAwareInterface;

use Sonata\AdminBundle\Validator\ErrorElement;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockServiceManager implements BlockServiceManagerInterface
{
    protected $logger;

    protected $blockServices;

    protected $debug;

    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param $debug
     * @param null|\Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, $debug, LoggerInterface $logger = null)
    {
        $this->debug         = $debug;
        $this->logger        = $logger;
        $this->blockServices = array();
        $this->container     = $container;
    }

    /**
     * Render a specialize block
     *
     * @throws \Exception|\RuntimeException
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return \Symfony\Component\HttpFoundation\Response|void
     */
    public function renderBlock(BlockInterface $block)
    {
        if ($this->logger) {
            $this->logger->info(sprintf('[cms::renderBlock] block.id=%d, block.type=%s ', $block->getId(), $block->getType()));
        }

        $response = new Response;

        try {
            $service = $this->getBlockService($block);

            if ($service instanceof BlockManagerAwareInterface) {
                $service->setBlockManager($this);
            }

            $service->load($block); // load the block

            $response = $service->execute($block, $response);

            if (!$response instanceof Response) {
                throw new \RuntimeException('A block service must return a Response object');
            }

        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->crit(sprintf('[cms::renderBlock] block.id=%d - error while rendering block - %s', $block->getId(), $e->getMessage()));
            }

            if ($this->debug) {
                throw $e;
            }

            $response->setPrivate();
        }

        return $response;
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
     * Return the block service linked to the link
     *
     * @throws \RuntimeException
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return \Sonata\BlockBundle\Block\BlockServiceInterface
     */
    public function getBlockService(BlockInterface $block)
    {
        $this->loadService($block->getType());

        return $this->blockServices[$block->getType()];
    }

    /**
     *
     * @param sring $id
     * @return boolean
     */
    public function hasBlockService($id)
    {
        return isset($this->blockServices[$id]) ? true : false;
    }

    /**
     * @param string $name
     * @param string $service
     * @return void
     */
    public function addBlockService($name, $service)
    {
        $this->blockServices[$name] = $service;
    }

    /**
     * @param array $blockServices
     * @return void
     */
    public function setBlockServices(array $blockServices)
    {
        $this->blockServices = $blockServices;
    }

    /**
     * @return array
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
     * @return array
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
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return
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