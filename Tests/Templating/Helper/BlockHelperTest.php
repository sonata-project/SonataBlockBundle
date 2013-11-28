<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Templating\Helper;

use Sonata\BlockBundle\Templating\Helper\BlockHelper;

class BlockTest extends \PHPUnit_Framework_TestCase
{
    public function testRenderEvent()
    {

        $blockServiceManager = $this->getMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $cacheBlocks = array();
        $blockRenderer = $this->getMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $blockContextManager = $this->getMock('Sonata\BlockBundle\Block\BlockContextManagerInterface');
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $helper = new BlockHelper($blockServiceManager, $cacheBlocks, $blockRenderer, $blockContextManager, $eventDispatcher);

        $this->assertEquals('', $helper->renderEvent('my.event'));
    }
}