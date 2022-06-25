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

namespace Sonata\BlockBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Model\Block;

final class BlockTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testGetTtl(): void
    {
        $block = new Block();

        static::assertFalse($block->hasChildren());

        $child1 = $this->createMock(Block::class);
        $child1->expects(static::once())->method('getTtl')->willReturn(100);

        $child2 = $this->createMock(Block::class);
        $child2->expects(static::once())->method('getTtl')->willReturn(50);

        $child3 = $this->createMock(Block::class);
        $child3->expects(static::once())->method('getTtl')->willReturn(65);

        $block->addChildren($child1);
        $block->addChildren($child2);
        $block->addChildren($child3);

        static::assertSame(50, $block->getTtl());

        static::assertTrue($block->hasChildren());
    }

    public function testSetterGetter(): void
    {
        $time = new \DateTime();
        $parent = $this->createMock(Block::class);

        $block = new Block();
        $block->setName('my.block.name');
        $block->setCreatedAt($time);
        $block->setUpdatedAt($time);
        $block->setEnabled(true);
        $block->setPosition(1);
        $block->setType('foo.bar');
        $block->setParent($parent);

        static::assertSame('my.block.name', $block->getName());
        static::assertSame($time, $block->getCreatedAt());
        static::assertSame($time, $block->getUpdatedAt());
        static::assertTrue($block->getEnabled());
        static::assertSame(1, $block->getPosition());
        static::assertSame('foo.bar', $block->getType());
        static::assertSame($parent, $block->getParent());
    }

    public function testSetting(): void
    {
        $block = new Block();
        $block->setSetting('foo', 'bar');

        static::assertSame('void', $block->getSetting('fake', 'void'));
        static::assertNull($block->getSetting('fake'));
        static::assertSame('bar', $block->getSetting('foo'));
    }
}
