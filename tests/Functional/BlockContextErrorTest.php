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

namespace Sonata\BlockBundle\Tests\Functional;

use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The test kernel runs without debug mode, so block errors are handled like in production.
 */
final class BlockContextErrorTest extends KernelTestCase
{
    public function testStoredBlockWithUnknownTypeDoesNotBreakThePage(): void
    {
        $block = new Block();
        $block->setId(1);
        $block->setType('app.block.removed_service');

        // in production, block errors are handled by the exception strategy (default: ignored and logged)
        static::assertSame('', $this->getHelper()->render($block));
    }

    public function testBlocksFromConfigurationStillReportErrors(): void
    {
        // blocks configured in a template are written by developers, errors must stay visible
        $this->expectException(\RuntimeException::class);

        $this->getHelper()->render(['type' => 'app.block.removed_service']);
    }

    private function getHelper(): BlockHelper
    {
        $helper = static::getContainer()->get('sonata.block.templating.helper');
        static::assertInstanceOf(BlockHelper::class, $helper);

        return $helper;
    }
}
