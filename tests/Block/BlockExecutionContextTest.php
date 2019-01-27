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

namespace Sonata\BlockBundle\Tests\Block;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContext;

class BlockExecutionContextTest extends TestCase
{
    public function testBasicFeature(): void
    {
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $blockContext = new BlockContext($block, [
            'hello' => 'world',
        ]);

        $this->assertSame('world', $blockContext->getSetting('hello'));
        $this->assertSame(['hello' => 'world'], $blockContext->getSettings());

        $this->assertSame($block, $blockContext->getBlock());
    }

    public function testInvalidParameter(): void
    {
        $this->expectException(\RuntimeException::class);

        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $blockContext = new BlockContext($block);

        $blockContext->getSetting('fake');
    }
}
