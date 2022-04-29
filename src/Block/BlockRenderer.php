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
use Sonata\BlockBundle\Exception\Strategy\StrategyManagerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the execution and rendering of a block.
 *
 * This function render a block and make sure the cacheable information are correctly retrieved
 * and set to the upper response (container can have child blocks, so the smallest ttl from a child
 * must be used in the container).
 */
final class BlockRenderer implements BlockRendererInterface
{
    private BlockServiceManagerInterface $blockServiceManager;

    private StrategyManagerInterface $exceptionStrategyManager;

    private ?LoggerInterface $logger;

    /**
     * This property hold the last response available from the child or sibling block
     * The cacheable attributes must be cascaded to the parent.
     */
    private ?Response $lastResponse = null;

    /**
     * @param BlockServiceManagerInterface $blockServiceManager      Block service manager
     * @param StrategyManagerInterface     $exceptionStrategyManager Exception strategy manager
     * @param LoggerInterface              $logger                   Logger class
     */
    public function __construct(
        BlockServiceManagerInterface $blockServiceManager,
        StrategyManagerInterface $exceptionStrategyManager,
        ?LoggerInterface $logger = null
    ) {
        $this->blockServiceManager = $blockServiceManager;
        $this->exceptionStrategyManager = $exceptionStrategyManager;
        $this->logger = $logger;
    }

    public function render(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $block = $blockContext->getBlock();

        if (null !== $this->logger) {
            $this->logger->info(
                sprintf('[cms::renderBlock] block.id=%d, block.type=%s', $block->getId() ?? '', $block->getType() ?? '')
            );
        }

        try {
            $service = $this->blockServiceManager->get($block);
            $service->load($block);

            $response = $service->execute($blockContext, $this->createResponse($blockContext, $response));

            $response = $this->addMetaInformation($response, $blockContext);
        } catch (\Throwable $exception) {
            if (null !== $this->logger) {
                $this->logger->error(sprintf(
                    '[cms::renderBlock] block.id=%d - error while rendering block - %s',
                    $block->getId() ?? '',
                    $exception->getMessage()
                ), compact('exception'));
            }

            // reseting the state object
            $this->lastResponse = null;

            $response = $this->exceptionStrategyManager->handleException($exception, $blockContext->getBlock(), $response);
        }

        return $response;
    }

    private function createResponse(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        if (null === $response) {
            $response = new Response();
        }

        // set the ttl from the block instance, this can be changed by the BlockService
        if (($ttl = $blockContext->getBlock()->getTtl()) > 0) {
            $response->setTtl($ttl);
        }

        return $response;
    }

    /**
     * This method is responsible to cascade ttl to the parent block.
     */
    private function addMetaInformation(Response $response, BlockContextInterface $blockContext): Response
    {
        // a response exists, use it
        if (null !== $this->lastResponse && $this->lastResponse->isCacheable()) {
            $lastResponseTtl = $this->lastResponse->getTtl();
            if (null !== $lastResponseTtl) {
                $response->setTtl($lastResponseTtl);
            }
            $response->setPublic();
        } elseif (null !== $this->lastResponse) { // not cacheable
            $response->setPrivate();
            $response->setTtl(0);
            $response->headers->removeCacheControlDirective('s-maxage');
            $response->headers->removeCacheControlDirective('maxage');
        }

        // no more children available in the stack, reseting the state object
        if (!$blockContext->getBlock()->hasParent()) {
            $this->lastResponse = null;
        } else { // contains a parent so storing the response
            $this->lastResponse = $response;
        }

        return $response;
    }
}
