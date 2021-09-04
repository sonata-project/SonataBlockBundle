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

namespace Sonata\BlockBundle\Tests;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\BlockInterface;

final class BlockEventTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testBlockEvent(): void
    {
        $blockEvent = new BlockEvent();

        static::assertEmpty($blockEvent->getSettings());

        $blockEvent->addBlock($this->createMock(BlockInterface::class));

        static::assertCount(1, $blockEvent->getBlocks());

        $blockEvent->addBlock($this->createMock(BlockInterface::class));
        static::assertCount(2, $blockEvent->getBlocks());

        static::assertNull($blockEvent->getSetting('fake'));
        static::assertSame(1, $blockEvent->getSetting('fake', 1));
    }
}
