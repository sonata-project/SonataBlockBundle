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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class BlockServiceManager implements BlockServiceManagerInterface
{
    protected $logger;

    protected $blockServices;

    protected $debug;

    /**
     * @param $debug
     * @param null|\Symfony\Component\HttpKernel\Log\LoggerInterface $logger
     */
    public function __construct($debug, LoggerInterface $logger = null)
    {
        $this->debug         = $debug;
        $this->logger        = $logger;
        $this->blockServices = array();
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
     * Return the block service linked to the link
     *
     * @throws \RuntimeException
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return \Sonata\BlockBundle\Block\BlockServiceInterface
     */
    public function getBlockService(BlockInterface $block)
    {
        if (!$this->hasBlockService($block->getType())) {
            if ($this->debug) {
                throw new \RuntimeException(sprintf('The block service `%s` referenced in the block `%s` does not exists', $block->getType(), $block->getId()));
            }

            if ($this->logger){
                $this->logger->crit(sprintf('[cms::getBlockService] block.id=%d - service:%s does not exists', $block->getId(), $block->getType()));
            }

            return false;
        }

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
     * @param $name
     * @param \Sonata\BlockBundle\Block\BlockServiceInterface $service
     * @return void
     */
    public function addBlockService($name, BlockServiceInterface $service)
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
        return $this->blockServices;
    }
}