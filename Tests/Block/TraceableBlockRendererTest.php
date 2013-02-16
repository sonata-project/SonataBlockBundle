<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Block;

use Sonata\BlockBundle\Block\TraceableBlockRenderer;
use Symfony\Component\HttpFoundation\Response;

class TraceableBlockRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $renderer = $this->getMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $renderer->expects($this->once())->method('render')->will($this->returnValue(new Response()));

        $traceable = new TraceableBlockRenderer($renderer, array());

        $block = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->any())->method('getId')->will($this->returnValue(42));
        $block->expects($this->any())->method('getType')->will($this->returnValue('mytype'));

        $traceable->render($block);

        $traces = $traceable->getTraces();

        $this->assertEquals(1, count($traces));
        $this->assertArrayHasKey(42, $traces);
        $this->assertEquals('mytype', $traces[42]['type']);
    }
}
