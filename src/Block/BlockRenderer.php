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
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles the execution and rendering of a block.
 */
final class BlockRenderer implements BlockRendererInterface
{
    /**
     * Blocks that are currently being rendered, indexed by their object id.
     *
     * @var array<int, true>
     */
    private array $renderingBlocks = [];

    /**
     * @param int $maxNestingLevel Maximum number of blocks rendered inside each other
     */
    public function __construct(
        private BlockServiceManagerInterface $blockServiceManager,
        private StrategyManagerInterface $exceptionStrategyManager,
        private ?LoggerInterface $logger = null,
        private int $maxNestingLevel = 50,
    ) {
    }

    public function render(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $block = $blockContext->getBlock();

        if (null !== $this->logger) {
            $this->logger->info(
                \sprintf('[cms::renderBlock] block.id=%s, block.type=%s', $block->getId() ?? '', $block->getType() ?? '')
            );
        }

        // A block that contains itself (directly or through its children) would be rendered until the
        // PHP process crashes, so stop when the same block is rendered again or the nesting is too deep.
        $objectId = spl_object_id($block);
        if (isset($this->renderingBlocks[$objectId]) || \count($this->renderingBlocks) >= $this->maxNestingLevel) {
            return $this->handleException(new \RuntimeException(\sprintf(
                'The block %s cannot be rendered, it is rendered inside itself or nested more than %d levels deep.',
                $block->getId() ?? '',
                $this->maxNestingLevel
            )), $block, $response);
        }

        $this->renderingBlocks[$objectId] = true;

        try {
            $service = $this->blockServiceManager->get($block);
            $service->load($block);

            $response = $service->execute($blockContext, $response ?? new Response());
        } catch (\Throwable $exception) {
            $response = $this->handleException($exception, $block, $response);
        } finally {
            unset($this->renderingBlocks[$objectId]);
        }

        return $response;
    }

    private function handleException(\Throwable $exception, BlockInterface $block, ?Response $response): Response
    {
        if (null !== $this->logger) {
            $this->logger->error(\sprintf(
                '[cms::renderBlock] block.id=%s - error while rendering block - %s',
                $block->getId() ?? '',
                $exception->getMessage()
            ), compact('exception'));
        }

        return $this->exceptionStrategyManager->handleException($exception, $block, $response);
    }
}
