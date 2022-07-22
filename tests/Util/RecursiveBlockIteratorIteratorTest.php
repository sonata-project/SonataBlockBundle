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

namespace Sonata\BlockBundle\Tests\Util;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Util\RecursiveBlockIteratorIterator;

final class RecursiveBlockIteratorIteratorTest extends TestCase
{
    public function testInterface(): void
    {
        $block2 = $this->createMock(BlockInterface::class);
        $block2->expects(static::any())->method('getType')->willReturn('block2');
        $block2->expects(static::once())->method('hasChild')->willReturn(false);

        $block3 = $this->createMock(BlockInterface::class);
        $block3->expects(static::any())->method('getType')->willReturn('block3');
        $block3->expects(static::once())->method('hasChild')->willReturn(false);

        $block1 = $this->createMock(BlockInterface::class);
        $block1->expects(static::any())->method('getType')->willReturn('block1');
        $block1->expects(static::once())->method('hasChild')->willReturn(true);
        $block1->expects(static::any())->method('getChildren')->willReturn(new ArrayCollection([
            $block2,
            $block3,
        ]));

        $block4 = $this->createMock(BlockInterface::class);
        $block4->expects(static::any())->method('getType')->willReturn('block4');
        $block4->expects(static::any())->method('hasChild')->willReturn(false);

        $i = new RecursiveBlockIteratorIterator([$block1, $block4]);

        $blocks = [];
        foreach ($i as $block) {
            $blocks[] = $block;
        }

        static::assertCount(4, $blocks);
    }
}
