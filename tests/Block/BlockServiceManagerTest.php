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
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class BlockServiceManagerTest extends TestCase
{
    public function testGetBlockService(): void
    {
        $service = $this->createMock(BlockServiceInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->willReturn($service);

        $manager = new BlockServiceManager($container);

        $manager->add('test', 'test');

        $block = $this->createMock(BlockInterface::class);
        $block->expects($this->any())->method('getType')->willReturn('test');

        $this->assertInstanceOf(\get_class($service), $manager->get($block));
    }

    public function testInvalidServiceType(): void
    {
        $this->expectException(\RuntimeException::class);

        $service = $this->createMock('stdClass');

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->willReturn($service);

        $manager = new BlockServiceManager($container);

        $manager->add('test', 'test');

        $block = $this->createMock(BlockInterface::class);
        $block->expects($this->any())->method('getType')->willReturn('test');

        $this->assertInstanceOf(\get_class($service), $manager->get($block));
    }

    public function testGetBlockServiceException(): void
    {
        $this->expectException(\RuntimeException::class);

        $container = $this->createMock(ContainerInterface::class);

        $manager = new BlockServiceManager($container);

        $block = $this->createMock(BlockInterface::class);
        $block->expects($this->any())->method('getType')->willReturn('fakse');

        $manager->get($block);
    }

    public function testGetEmptyListFromInvalidContext(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $manager = new BlockServiceManager($container);

        $service = $this->createMock(BlockServiceInterface::class);

        $manager->add('foo.bar', $service);

        $this->assertEmpty($manager->getServicesByContext('fake'));
    }

    public function testGetListFromValidContext(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $manager = new BlockServiceManager($container);

        $service = $this->createMock(BlockServiceInterface::class);

        $manager->add('foo.bar', $service, ['fake']);

        $this->assertNotEmpty($manager->getServicesByContext('fake'));
    }
}
