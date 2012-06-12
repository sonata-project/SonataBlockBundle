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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class BlockRenderer implements BlockRendererInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * @var BlockServiceManagerInterface
     */
    protected $blockServiceManager;

    /**
     * @param BlockServiceManagerInterface $blockServiceManager
     * @param LoggerInterface $logger
     * @param boolean $debug
     */
    public function __construct(BlockServiceManagerInterface $blockServiceManager, LoggerInterface $logger, $debug)
    {
        $this->debug  = $debug;
        $this->logger = $logger;
        $this->blockServiceManager = $blockServiceManager;
    }

    /**
     * {@inheritdoc}
     */
    public function render(BlockInterface $block, Response $response = null)
    {
        if ($this->logger) {
            $this->logger->info(sprintf('[cms::renderBlock] block.id=%d, block.type=%s ', $block->getId(), $block->getType()));
        }

        try {
            $service = $this->blockServiceManager->get($block);

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

            $response = new Response;
            $response->setPrivate();
        }

        return $response;
    }
}