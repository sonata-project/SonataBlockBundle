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
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Component\HttpFoundation\Response;

class BlockHelperTest extends TestCase
{
    public function testRenderEventWithNoListener()
    {
        $blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $blockRenderer = $this->createMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $blockContextManager = $this->createMock('Sonata\BlockBundle\Block\BlockContextManagerInterface');
        $eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher->expects($this->once())->method('dispatch')->will($this->returnCallback(function ($name, BlockEvent $event) {
            return $event;
        }));

        $helper = new BlockHelper($blockServiceManager, [], $blockRenderer, $blockContextManager, $eventDispatcher);

        $this->assertSame('', $helper->renderEvent('my.event'));
    }

    /**
     * @group legacy
     */
    public function testRenderEventWithListeners()
    {
        $blockService = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $blockService->expects($this->once())->method('getJavascripts')->will($this->returnValue([
            '/js/base.js',
        ]));
        $blockService->expects($this->once())->method('getStylesheets')->will($this->returnValue([
            '/css/base.css',
        ]));

        $blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $blockServiceManager->expects($this->any())->method('get')->will($this->returnValue($blockService));

        $blockRenderer = $this->createMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $blockRenderer->expects($this->once())->method('render')->will($this->returnValue(new Response('<span>test</span>')));

        $blockContextManager = $this->createMock('Sonata\BlockBundle\Block\BlockContextManagerInterface');
        $blockContextManager->expects($this->once())->method('get')->will($this->returnCallback(function (BlockInterface $block) {
            $context = new BlockContext($block, $block->getSettings());

            return $context;
        }));

        $eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $eventDispatcher->expects($this->once())->method('dispatch')->will($this->returnCallback(function ($name, BlockEvent $event) {
            $block = new Block();
            $block->setId(1);
            $block->setSettings([
                'use_cache' => false,
            ]);
            $block->setType('test');
            $event->addBlock($block);

            return $event;
        }));

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
