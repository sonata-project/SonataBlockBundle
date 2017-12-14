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
use Sonata\BlockBundle\Event\TextBlockListener;

class TextBlockListenerTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new BlockEvent();

        $listener = new TextBlockListener();
        $listener->onBlock($event);

        $this->assertCount(1, $event->getBlocks());

        $blocks = $event->getBlocks();

        $this->assertEquals('This block is coming from inline event from the template', $blocks[0]->getSetting('content'));
    }

    public function testEventWithAdmin(): void
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())->method('getSubject');
        $admin->expects($this->once())->method('toString')->will($this->returnValue('fake object'));

        $event = new BlockEvent([
            'admin' => $admin,
            'action' => 'edit',
        ]);

        $listener = new TextBlockListener();
        $listener->onBlock($event);

        $this->assertCount(1, $event->getBlocks());

        $blocks = $event->getBlocks();

        $this->assertEquals('<p class=\'well\'>The admin subject is <strong>fake object</strong></p>', $blocks[0]->getSetting('content'));
    }
}
