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

        $manager = new BlockServiceManager($container, true);

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

        $manager = new BlockServiceManager($container, true);

        $manager->add('test', 'test');

        $block = $this->createMock(BlockInterface::class);
        $block->expects($this->any())->method('getType')->willReturn('test');

        $this->assertInstanceOf(\get_class($service), $manager->get($block));
    }

    public function testGetBlockServiceException(): void
    {
        $this->expectException(\RuntimeException::class);

        $container = $this->createMock(ContainerInterface::class);

        $manager = new BlockServiceManager($container, true);

        $block = $this->createMock(BlockInterface::class);
        $block->expects($this->any())->method('getType')->willReturn('fakse');

        $manager->get($block);
    }

    public function testGetEmptyListFromInvalidContext(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $manager = new BlockServiceManager($container, true);

        $service = $this->createMock(BlockServiceInterface::class);

        $manager->add('foo.bar', $service);

        $this->assertEmpty($manager->getServicesByContext('fake'));
    }

    public function testGetListFromValidContext(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $manager = new BlockServiceManager($container, true);

        $service = $this->createMock(BlockServiceInterface::class);

        $manager->add('foo.bar', $service, ['fake']);

        $this->assertNotEmpty($manager->getServicesByContext('fake'));
    }

    public function testOrderServices(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $manager = new BlockServiceManager($container, true);

        $serviceAbc = $this->createMock(BlockServiceInterface::class);
        $serviceAbc->expects($this->any())->method('getName')->willReturn('GHI');
        $manager->add('ghi', $serviceAbc);

        $serviceAbc = $this->createMock(BlockServiceInterface::class);
        $serviceAbc->expects($this->any())->method('getName')->willReturn('ABC');
        $manager->add('abc', $serviceAbc);

        $serviceAbc = $this->createMock(BlockServiceInterface::class);
        $serviceAbc->expects($this->any())->method('getName')->willReturn('DEF');
        $manager->add('def', $serviceAbc);

        $services = array_keys($manager->getServices());

        $this->assertSame('abc', $services[0], 'After order, the first service should be "ABC"');
        $this->assertSame('def', $services[1], 'After order, the second service should be "DEF"');
        $this->assertSame('ghi', $services[2], 'After order, the third service should be "GHI"');
    }
}
