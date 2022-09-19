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

namespace Sonata\BlockBundle\Tests\Exception\Renderer;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockServiceManager;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Exception\BlockNotFoundException;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Jérôme Engeln
 */
final class BlockNotFoundRendererTest extends TestCase
{
    public function testRenderWithBlockNotFoundException(): void
    {
        $this->expectException(BlockNotFoundException::class);

        $service = $this->createMock(BlockServiceInterface::class);

        $container = new Container();

        $container->set('existing-block', $service);

        $manager = new BlockServiceManager($container, []);
        $manager->add('existing-block', 'existing-block');

        $block = $this->createMock(BlockInterface::class);
        $block->setType('non-existing-block');
        $block->expects(static::any())->method('getType')->willReturn('non-existing-block');
    }
}
