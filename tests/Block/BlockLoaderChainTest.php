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
use Sonata\BlockBundle\Block\BlockLoaderChain;

class BlockLoaderChainTest extends TestCase
{
    public function testBlockNotFoundException()
    {
        $this->expectException(\Sonata\BlockBundle\Exception\BlockNotFoundException::class);

        $loader = new BlockLoaderChain([]);
        $loader->load('foo');
    }

    public function testLoaderWithSupportedLoader()
    {
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $loader = $this->createMock('Sonata\BlockBundle\Block\BlockLoaderInterface');
        $loader->expects($this->once())->method('support')->will($this->returnValue(true));
        $loader->expects($this->once())->method('load')->will($this->returnValue($block));

        $loaderChain = new BlockLoaderChain([$loader]);

        $this->assertTrue($loaderChain->support('foo'));

        $this->assertSame($block, $loaderChain->load('foo'));
    }

    public function testLoaderWithUnSupportedLoader()
    {
        $this->expectException(\Sonata\BlockBundle\Exception\BlockNotFoundException::class);

        $loader = $this->createMock('Sonata\BlockBundle\Block\BlockLoaderInterface');
        $loader->expects($this->once())->method('support')->will($this->returnValue(false));
        $loader->expects($this->never())->method('load');

        $loaderChain = new BlockLoaderChain([$loader]);

        $this->assertTrue($loaderChain->support('foo'));

        $loaderChain->load('foo');
    }
}
