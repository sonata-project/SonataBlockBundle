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

class BlockEventTest extends TestCase
{
    public function testBlockEvent(): void
    {
        $blockEvent = new BlockEvent();

        $this->assertEmpty($blockEvent->getSettings());

        $blockEvent->addBlock($this->createMock('Sonata\BlockBundle\Model\BlockInterface'));

        $this->assertCount(1, $blockEvent->getBlocks());

        $blockEvent->addBlock($this->createMock('Sonata\BlockBundle\Model\BlockInterface'));
        $this->assertCount(2, $blockEvent->getBlocks());

        $this->assertNull($blockEvent->getSetting('fake'));
        $this->assertSame(1, $blockEvent->getSetting('fake', 1));
    }
}
