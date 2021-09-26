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

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class BlockHelperTest extends TestCase
{
    public function testRenderEventWithNoListener(): void
    {
        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockRenderer = $this->createMock(BlockRendererInterface::class);
        $blockContextManager = $this->createMock(BlockContextManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(static::once())->method('dispatch')->willReturnCallback(static function ($event): BlockEvent {
            return $event;
        });

        $helper = new BlockHelper($blockServiceManager, [], $blockRenderer, $blockContextManager, $eventDispatcher);

        static::assertSame('', $helper->renderEvent('my.event'));
    }

    /**
     * @group legacy
     */
    public function testRenderEventWithListeners(): void
    {
        $blockService = $this->createMock(BlockServiceInterface::class);

        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockServiceManager->expects(static::any())->method('get')->willReturn($blockService);

        $blockRenderer = $this->createMock(BlockRendererInterface::class);
        $blockRenderer->expects(static::once())->method('render')->willReturn(new Response('<span>test</span>'));

        $blockContextManager = $this->createMock(BlockContextManagerInterface::class);
        $blockContextManager->expects(static::once())->method('get')->willReturnCallback(
            static function (BlockInterface $block): BlockContext {
                return new BlockContext($block, $block->getSettings());
            }
        );

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(static::once())->method('dispatch')->willReturnCallback(static function (BlockEvent $event): BlockEvent {
            $block = new Block();
            $block->setId(1);
            $block->setSettings([
                'use_cache' => false,
            ]);
            $block->setType('test');

            $event->addBlock($block);

            return $event;
        });

        $helper = new BlockHelper($blockServiceManager, [], $blockRenderer, $blockContextManager, $eventDispatcher);

        static::assertSame('<span>test</span>', $helper->renderEvent('my.event'));
    }
}
