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

namespace Sonata\BlockBundle\Tests\Templating\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class BlockHelperTest extends TestCase
{
    public function testRenderEventWithNoListener(): void
    {
        $blockRenderer = static::createStub(BlockRendererInterface::class);
        $blockContextManager = static::createStub(BlockContextManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(static::once())->method('dispatch')->willReturnCallback(static fn (BlockEvent $event): BlockEvent => $event);

        $helper = new BlockHelper($blockRenderer, $blockContextManager, $eventDispatcher);

        static::assertSame('', $helper->renderEvent('my.event'));
    }

    public function testRenderEventWithListeners(): void
    {
        $blockRenderer = $this->createMock(BlockRendererInterface::class);
        $blockRenderer->expects(static::once())->method('render')->willReturn(new Response('<span>test</span>'));

        $blockContextManager = $this->createMock(BlockContextManagerInterface::class);
        $blockContextManager->expects(static::once())->method('get')->willReturnCallback(
            static fn (BlockInterface $block): BlockContext => new BlockContext($block, $block->getSettings())
        );

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(static::once())->method('dispatch')->willReturnCallback(static function (BlockEvent $event): BlockEvent {
            $block = new Block();
            $block->setId(1);
            $block->setType('test');

            $event->addBlock($block);

            return $event;
        });

        $helper = new BlockHelper($blockRenderer, $blockContextManager, $eventDispatcher);

        static::assertSame('<span>test</span>', $helper->renderEvent('my.event'));
    }

    /**
     * @return iterable<string, array{int|string|null, int|string|null}>
     */
    public static function provideNestedBlocksAreTracedCases(): iterable
    {
        yield 'blocks without id' => [null, null];
        yield 'blocks with the same id' => [5, 5];
        yield 'blocks with different ids' => [1, 2];
    }

    #[DataProvider('provideNestedBlocksAreTracedCases')]
    public function testNestedBlocksAreTraced(int|string|null $parentId, int|string|null $childId): void
    {
        $parent = new Block();
        $parent->setType('parent');
        $child = new Block();
        $child->setType('child');
        if (null !== $parentId) {
            $parent->setId($parentId);
        }
        if (null !== $childId) {
            $child->setId($childId);
        }

        $blockContextManager = static::createStub(BlockContextManagerInterface::class);
        $blockContextManager->method('get')->willReturnCallback(
            static fn (BlockInterface $block): BlockContext => new BlockContext($block)
        );

        $blockRenderer = static::createStub(BlockRendererInterface::class);
        $helper = new BlockHelper($blockRenderer, $blockContextManager, static::createStub(EventDispatcherInterface::class), new Stopwatch());

        $blockRenderer->method('render')->willReturnCallback(
            static function (BlockContext $blockContext) use ($helper, $child): Response {
                // the parent renders its child, like a container block does in its template
                if ('parent' === $blockContext->getBlock()->getType()) {
                    return new Response('parent('.$helper->render($child).')');
                }

                return new Response('child');
            }
        );

        static::assertSame('parent(child)', $helper->render($parent));
    }
}
