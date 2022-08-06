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
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Util\RecursiveBlockIteratorIterator;

final class RecursiveBlockIteratorIteratorTest extends TestCase
{
    public function testInterface(): void
    {
        $block1 = new Block();
        $block2 = new Block();
        $block3 = new Block();
        $block4 = new Block();

        $block1->addChild($block2);
        $block1->addChild($block3);

        $i = new RecursiveBlockIteratorIterator([$block1, $block4]);

        $blocks = [];
        foreach ($i as $block) {
            $blocks[] = $block;
        }

        static::assertCount(4, $blocks);
    }
}
