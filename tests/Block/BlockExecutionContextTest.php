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
use Sonata\BlockBundle\Model\BlockInterface;

final class BlockExecutionContextTest extends TestCase
{
    public function testBasicFeature(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockContext = new BlockContext($block, [
            'hello' => 'world',
            'template' => 'fake_template',
        ]);

        static::assertSame('world', $blockContext->getSetting('hello'));
        static::assertSame('fake_template', $blockContext->getTemplate());
        static::assertSame([
            'hello' => 'world',
            'template' => 'fake_template',
        ], $blockContext->getSettings());

        static::assertSame($block, $blockContext->getBlock());
    }

    public function testInvalidParameter(): void
    {
        $this->expectException(\RuntimeException::class);

        $block = $this->createMock(BlockInterface::class);

        $blockContext = new BlockContext($block, [
            'template' => 'fake_template',
        ]);

        $blockContext->getSetting('fake');
    }
}
