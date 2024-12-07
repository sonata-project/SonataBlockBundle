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
 */
final class BlockRenderer implements BlockRendererInterface
{
    public function __construct(
        private BlockServiceManagerInterface $blockServiceManager,
        private StrategyManagerInterface $exceptionStrategyManager,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function render(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $block = $blockContext->getBlock();

        if (null !== $this->logger) {
            $this->logger->info(
                \sprintf('[cms::renderBlock] block.id=%d, block.type=%s', $block->getId() ?? '', $block->getType() ?? '')
            );
        }

        try {
            $service = $this->blockServiceManager->get($block);
            $service->load($block);

            $response = $service->execute($blockContext, $response ?? new Response());
        } catch (\Throwable $exception) {
            if (null !== $this->logger) {
                $this->logger->error(\sprintf(
                    '[cms::renderBlock] block.id=%d - error while rendering block - %s',
                    $block->getId() ?? '',
                    $exception->getMessage()
                ), compact('exception'));
            }

            $response = $this->exceptionStrategyManager->handleException($exception, $blockContext->getBlock(), $response);
        }

        return $response;
    }
}
