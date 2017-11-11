<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Profiler\DataCollector;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Profiler\DataCollector\BlockDataCollector;

class BlockDataCollectorTest extends TestCase
{
    public function testBlockDataCollector()
    {
        $blockHelper = $this->createMock('Sonata\BlockBundle\Templating\Helper\BlockHelper');
        $blockHelper->expects($this->once())->method('getTraces')->will($this->returnValue([
            '_events' => [
                '1' => '2',
                '3' => '4',
            ],
            'test1' => [
                'type' => 'container',
            ],
            'test2' => [
                'type' => 'another_type',
            ],
        ]));

        $containerTypes = ["container"];

        $blockDataCollector = new BlockDataCollector($blockHelper, $containerTypes);

        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');

        $blockDataCollector->collect($request, $response);

        $expectedEvents = [
            '1' => '2',
            '3' => '4',
        ];
        $this->assertEquals($expectedEvents, $blockDataCollector->getEvents());

        $expectedBlocks = [
            '_events' => [
                '1' => '2',
                '3' => '4',
            ],
            'test1' => [
                'type' => 'container',
            ],
            'test2' => [
                'type' => 'another_type',
            ],
        ];
        $this->assertEquals($expectedBlocks, $blockDataCollector->getBlocks());

        $expectedContainers = [
            'test1' => [
                'type' => 'container',
            ]
        ];
        $this->assertEquals($expectedContainers, $blockDataCollector->getContainers());

        $expectedRealBlocks = [
            'test2' => [
                'type' => 'another_type',
            ]
        ];
        $this->assertEquals($expectedRealBlocks, $blockDataCollector->getRealBlocks());

        $blockDataCollector->reset();
        $this->assertEquals([], $blockDataCollector->getEvents());
        $this->assertEquals([], $blockDataCollector->getBlocks());
        $this->assertEquals([], $blockDataCollector->getContainers());
        $this->assertEquals([], $blockDataCollector->getRealBlocks());
    }
}
