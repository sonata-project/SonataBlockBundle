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

class BlockServiceManagerTest extends TestCase
{
    public function testGetBlockService(): void
    {
        $service = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())->method('get')->will($this->returnValue($service));

        $manager = new BlockServiceManager($container, true);

        $manager->add('test', 'test');

        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->any())->method('getType')->will($this->returnValue('test'));

        $this->assertInstanceOf(get_class($service), $manager->get($block));
    }

    public function testInvalidServiceType(): void
    {
        $this->expectException(\RuntimeException::class);

        $service = $this->createMock('stdClass');

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())->method('get')->will($this->returnValue($service));

        $manager = new BlockServiceManager($container, true);

        $manager->add('test', 'test');

        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->any())->method('getType')->will($this->returnValue('test'));

        $this->assertInstanceOf(get_class($service), $manager->get($block));
    }

    public function testGetBlockServiceException(): void
    {
        $this->expectException(\RuntimeException::class);

        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $manager = new BlockServiceManager($container, true);

        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->any())->method('getType')->will($this->returnValue('fakse'));

        $manager->get($block);
    }

    public function testGetEmptyListFromInvalidContext(): void
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $manager = new BlockServiceManager($container, true);

        $service = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');

        $manager->add('foo.bar', $service);

        $this->assertEmpty($manager->getServicesByContext('fake'));
    }

    public function testGetListFromValidContext(): void
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $manager = new BlockServiceManager($container, true);

        $service = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');

        $manager->add('foo.bar', $service, ['fake']);

        $this->assertNotEmpty($manager->getServicesByContext('fake'));
    }

    public function testOrderServices(): void
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $manager = new BlockServiceManager($container, true);

        $serviceAbc = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $serviceAbc->expects($this->any())->method('getName')->will($this->returnValue('GHI'));
        $manager->add('ghi', $serviceAbc);

        $serviceAbc = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $serviceAbc->expects($this->any())->method('getName')->will($this->returnValue('ABC'));
        $manager->add('abc', $serviceAbc);

        $serviceAbc = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $serviceAbc->expects($this->any())->method('getName')->will($this->returnValue('DEF'));
        $manager->add('def', $serviceAbc);

        $services = array_keys($manager->getServices());

        $this->assertEquals('abc', $services[0], 'After order, the first service should be "ABC"');
        $this->assertEquals('def', $services[1], 'After order, the second service should be "DEF"');
        $this->assertEquals('ghi', $services[2], 'After order, the third service should be "GHI"');
    }
}
