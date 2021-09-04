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

namespace Sonata\BlockBundle\Tests\Profiler\DataCollector;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Profiler\DataCollector\BlockDataCollector;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class BlockDataCollectorTest extends TestCase
{
    public function testBlockDataCollector(): void
    {
        $blockHelper = $this->createStub(BlockHelper::class);
        $objectForBlock = new \DateTime();

        $blockDataCollector = new BlockDataCollector($blockHelper, ['container']);

        $expectedEvents = ['1' => '2', '3' => '4'];
        $expectedBlocks = [
            '_events' => ['1' => '2', '3' => '4'],
            'test1' => ['type' => 'container'],
            'test2' => ['type' => 'another_type', 'datetime' => $objectForBlock],
        ];
        $expectedContainers = ['test1' => ['type' => 'container']];
        $expectedRealBlocks = ['test2' => ['type' => 'another_type', 'datetime' => $objectForBlock]];

        $blockHelper->method('getTraces')->willReturn([
            '_events' => ['1' => '2', '3' => '4'],
            'test1' => ['type' => 'container'],
            'test2' => ['type' => 'another_type', 'datetime' => $objectForBlock],
        ]);

        $blockDataCollector->collect(new Request(), new Response());

        static::assertSame($expectedEvents, $blockDataCollector->getEvents());
        static::assertSame($expectedBlocks, $blockDataCollector->getBlocks());
        static::assertSame($expectedContainers, $blockDataCollector->getContainers());
        static::assertSame($expectedRealBlocks, $blockDataCollector->getRealBlocks());

        $blockDataCollector->reset();

        static::assertSame([], $blockDataCollector->getEvents());
        static::assertSame([], $blockDataCollector->getBlocks());
        static::assertSame([], $blockDataCollector->getContainers());
        static::assertSame([], $blockDataCollector->getRealBlocks());
    }
}
