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
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Exception\BlockServiceNotFoundException;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\Container;

final class BlockServiceManagerTest extends TestCase
{
    public function testGetBlockService(): void
    {
        $service = static::createStub(BlockServiceInterface::class);

        $container = new Container();
        $container->set('test', $service);

        $manager = new BlockServiceManager($container, []);
        $manager->add('test', 'test');

        $block = static::createStub(BlockInterface::class);
        $block->method('getType')->willReturn('test');

        static::assertSame($service, $manager->get($block));
    }

    public function testInvalidServiceType(): void
    {
        $this->expectException(BlockServiceNotFoundException::class);

        $service = static::createStub(\stdClass::class);

        $container = new Container();
        $container->set('test', $service);

        $manager = new BlockServiceManager($container, []);

        $manager->add('test', 'test');

        $block = static::createStub(BlockInterface::class);
        $block->method('getType')->willReturn('test');

        $manager->get($block);
    }

    public function testGetBlockServiceException(): void
    {
        $this->expectException(BlockServiceNotFoundException::class);

        $manager = new BlockServiceManager(new Container(), []);

        $block = static::createStub(BlockInterface::class);
        $block->method('getType')->willReturn('fakse');

        $manager->get($block);
    }

    public function testGetEmptyListFromInvalidContext(): void
    {
        $manager = new BlockServiceManager(new Container(), []);

        $service = static::createStub(BlockServiceInterface::class);

        $manager->add('foo.bar', $service);

        static::assertSame([], $manager->getServicesByContext('fake'));
    }

    public function testGetListFromValidContext(): void
    {
        $manager = new BlockServiceManager(new Container(), []);

        $service = static::createStub(BlockServiceInterface::class);

        $manager->add('foo.bar', $service, ['fake']);

        static::assertSame(['foo.bar' => $service], $manager->getServicesByContext('fake'));
    }

    public function testGetServicesByContextWithoutContainers(): void
    {
        $service = static::createStub(BlockServiceInterface::class);

        $container = new Container();
        $container->set('test', $service);

        $manager = new BlockServiceManager($container, ['foo']);

        $service = static::createStub(BlockServiceInterface::class);

        $manager->add('foo.bar', $service, ['bar']);

        static::assertSame([], $manager->getServicesByContext('fake', false));
    }

    public function testValidationStillWorksAfterAnError(): void
    {
        $failingService = static::createStubForIntersectionOfInterfaces([BlockServiceInterface::class, EditableBlockService::class]);
        $failingService->method('validate')->willThrowException(new \TypeError('Simulated type error in validate()'));

        $service = $this->createMockForIntersectionOfInterfaces([BlockServiceInterface::class, EditableBlockService::class]);
        $service->expects(static::exactly(2))->method('validate');

        $manager = new BlockServiceManager(new Container(), []);
        $manager->add('failing', $failingService);
        $manager->add('service', $service);

        $errorElement = (new \ReflectionClass(ErrorElement::class))->newInstanceWithoutConstructor();

        $manager->validate($errorElement, self::createBlock('service'));

        try {
            $manager->validate($errorElement, self::createBlock('failing'));
            static::fail('The error of the block service should be thrown.');
        } catch (\TypeError) {
        }

        // this block must still be validated, even after the error above
        $manager->validate($errorElement, self::createBlock('service'));
    }

    private static function createBlock(string $type): Block
    {
        $block = new Block();
        $block->setId(1);
        $block->setType($type);

        return $block;
    }
}
