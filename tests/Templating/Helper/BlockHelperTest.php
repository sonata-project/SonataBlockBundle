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
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\BlockBundle\Block\BlockServiceInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

final class BlockHelperTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testRenderWithCachedBlock(): void
    {
        $service = $this->createMock(BlockServiceInterface::class);
        $service->method('getJavascripts')->willReturn([]);
        $service->method('getStylesheets')->willReturn([]);
        $service->method('getCacheKeys')->willReturn([]);

        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockServiceManager->method('get')
            ->willReturn($service);

        $blockRenderer = $this->createMock(BlockRendererInterface::class);

        $block = $this->createMock(BlockInterface::class);

        $blockContext = $this->createMock(BlockContextInterface::class);
        $blockContext->method('getBlock')
            ->willReturn($block);
        $blockContext->method('getSetting')->willReturnCallback(static function (string $key) {
            if ('use_cache' === $key) {
                return true;
            }
            if ('extra_cache_keys' === $key) {
                return [];
            }

            return null;
        });

        $blockContextManager = $this->createMock(BlockContextManagerInterface::class);
        $blockContextManager->expects($this->once())->method('get')
            ->willReturn($blockContext);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('get')->willReturn(new Response());

        $cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPool->method('getItem')->willReturn($cacheItem);

        $helper = new BlockHelper($blockServiceManager, [], $blockRenderer, $blockContextManager, $eventDispatcher, $cacheItemPool);

        $this->assertSame('', $helper->render($block));
    }

    public function testRenderEventWithNoListener()
    {
        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockRenderer = $this->createMock(BlockRendererInterface::class);
        $blockContextManager = $this->createMock(BlockContextManagerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())->method('dispatch')->willReturnCallback(static function ($event, $name): BlockEvent {
            // NEXT_MAJOR: remove this check when dropping support for symfony/event-dispatcher 3.x
            if ($event instanceof BlockEvent) {
                return $event;
            }

            // $event is the second argument in symfony/event-dispatcher 3.x
            return $name;
        });

        $helper = new BlockHelper($blockServiceManager, [], $blockRenderer, $blockContextManager, $eventDispatcher);

        $this->assertSame('', $helper->renderEvent('my.event'));
    }

    /**
     * @group legacy
     */
    public function testRenderEventWithListeners()
    {
        $blockService = $this->createMock(BlockServiceInterface::class);
        $blockService->expects($this->once())->method('getJavascripts')->willReturn([
            '/js/base.js',
        ]);
        $blockService->expects($this->once())->method('getStylesheets')->willReturn([
            '/css/base.css',
        ]);

        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockServiceManager->expects($this->any())->method('get')->willReturn($blockService);

        $blockRenderer = $this->createMock(BlockRendererInterface::class);
        $blockRenderer->expects($this->once())->method('render')->willReturn(new Response('<span>test</span>'));

        $blockContextManager = $this->createMock(BlockContextManagerInterface::class);
        $blockContextManager->expects($this->once())->method('get')->willReturnCallback(static function (BlockInterface $block) {
            $context = new BlockContext($block, $block->getSettings());

            return $context;
        });

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects($this->once())->method('dispatch')->willReturnCallback(static function ($event, $name): BlockEvent {
            $block = new Block();
            $block->setId(1);
            $block->setSettings([
                'use_cache' => false,
            ]);
            $block->setType('test');

            // NEXT_MAJOR: remove this check when dropping support for symfony/event-dispatcher 3.x
            if ($event instanceof BlockEvent) {
                $event->addBlock($block);

                return $event;
            }

            // $event is the second argument in symfony/event-dispatcher 3.x
            $name->addBlock($block);

            return $name;
        });

        $helper = new BlockHelper($blockServiceManager, [], $blockRenderer, $blockContextManager, $eventDispatcher);

        $this->assertSame('<span>test</span>', $helper->renderEvent('my.event'));

        $this->assertSame(trim($helper->includeJavascripts('screen', '/application')), '<script src="/application/js/base.js" type="text/javascript"></script>');
        $this->assertSame(trim($helper->includeJavascripts('screen', '')), '<script src="/js/base.js" type="text/javascript"></script>');

        $this->assertSame($helper->includeStylesheets('screen', '/application'), <<<'EXPECTED'
<style type='text/css' media='screen'>
@import url(/application/css/base.css);
</style>
EXPECTED
);
        $this->assertSame($helper->includeStylesheets('screen', ''), <<<'EXPECTED'
<style type='text/css' media='screen'>
@import url(/css/base.css);
</style>
EXPECTED
);
    }
}
