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
use Sonata\BlockBundle\Block\BlockServiceManager;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Exception\BlockServiceNotFoundException;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\DependencyInjection\Container;

final class BlockServiceManagerTest extends TestCase
{
    public function testGetBlockService(): void
    {
        $service = $this->createMock(BlockServiceInterface::class);

        $container = new Container();
        $container->set('test', $service);

        $manager = new BlockServiceManager($container, []);
        $manager->add('test', 'test');

        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::any())->method('getType')->willReturn('test');

        static::assertSame($service, $manager->get($block));
    }

    public function testInvalidServiceType(): void
    {
        $this->expectException(BlockServiceNotFoundException::class);

        $service = $this->createMock(\stdClass::class);

        $container = new Container();
        $container->set('test', $service);

        $manager = new BlockServiceManager($container, []);

        $manager->add('test', 'test');

        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::any())->method('getType')->willReturn('test');

        $manager->get($block);
    }

    public function testGetBlockServiceException(): void
    {
        $this->expectException(BlockServiceNotFoundException::class);

        $manager = new BlockServiceManager(new Container(), []);

        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::any())->method('getType')->willReturn('fakse');

        $manager->get($block);
    }

    public function testGetEmptyListFromInvalidContext(): void
    {
        $manager = new BlockServiceManager(new Container(), []);

        $service = $this->createMock(BlockServiceInterface::class);

        $manager->add('foo.bar', $service);

        static::assertEmpty($manager->getServicesByContext('fake'));
    }

    public function testGetListFromValidContext(): void
    {
        $manager = new BlockServiceManager(new Container(), []);

        $service = $this->createMock(BlockServiceInterface::class);

        $manager->add('foo.bar', $service, ['fake']);

        static::assertNotEmpty($manager->getServicesByContext('fake'));
    }

    public function testGetServicesByContextWithoutContainers(): void
    {
        $service = $this->createMock(BlockServiceInterface::class);

        $container = new Container();
        $container->set('test', $service);

        $manager = new BlockServiceManager($container, ['foo']);

        $service = $this->createMock(BlockServiceInterface::class);

        $manager->add('foo.bar', $service, ['bar']);

        static::assertEmpty($manager->getServicesByContext('fake', false));
    }
}
