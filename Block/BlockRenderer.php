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

use Sonata\BlockBundle\Exception\Strategy\StrategyManagerInterface;

/**
 * Handles the execution and rendering of a block
 */
class BlockRenderer implements BlockRendererInterface
{
    /**
     * @var BlockServiceManagerInterface
     */
    protected $blockServiceManager;

    /**
     * @var StrategyManagerInterface
     */
    protected $exceptionStrategyManager;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor
     *
     * @param BlockServiceManagerInterface $blockServiceManager      Block service manager
     * @param StrategyManagerInterface     $exceptionStrategyManager Exception strategy manager
     * @param LoggerInterface              $logger                   Logger class
     * @param boolean                      $debug                    Whether in debug mode or not
     */
    public function __construct(BlockServiceManagerInterface $blockServiceManager, StrategyManagerInterface $exceptionStrategyManager, LoggerInterface $logger = null, $debug = false)
    {
        $this->blockServiceManager      = $blockServiceManager;
        $this->exceptionStrategyManager = $exceptionStrategyManager;
        $this->logger                   = $logger;
        $this->debug                    = $debug;
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
            $service->load($block);

            if (null === $response) {
                // In order to have the block's response's isCacheable() to true
                $response = new Response();
                $response->setTtl($block->getTtl());
            }

            $newResponse = $service->execute($block, $response);

            if (!$newResponse instanceof Response) {
                throw new \RuntimeException('A block service must return a Response object');
            }

        } catch (\Exception $exception) {
            if ($this->logger) {
                $this->logger->crit(sprintf('[cms::renderBlock] block.id=%d - error while rendering block - %s', $block->getId(), $exception->getMessage()));
            }
            $newResponse = $this->exceptionStrategyManager->handleException($exception, $block, $response);
        }

        return $newResponse;
    }
}
