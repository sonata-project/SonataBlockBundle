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

use Doctrine\Common\Util\ClassUtils;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContextManager;

class BlockContextManagerTest extends TestCase
{
    public function testGetWithValidData()
    {
        $service = $this->createMock('Sonata\BlockBundle\Block\Service\AbstractBlockService');

        $service->expects($this->once())->method('configureSettings');

        $blockLoader = $this->createMock('Sonata\BlockBundle\Block\BlockLoaderInterface');

        $serviceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $serviceManager->expects($this->once())->method('get')->will($this->returnValue($service));

        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->once())->method('getSettings')->will($this->returnValue([]));

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $blockContext = $manager->get($block);

        $this->assertInstanceOf('Sonata\BlockBundle\Block\BlockContextInterface', $blockContext);

        $this->assertEquals([
            'use_cache' => true,
            'extra_cache_keys' => [],
            'attr' => [],
            'template' => false,
            'ttl' => 0,
        ], $blockContext->getSettings());
    }

    public function testGetWithSettings()
    {
        $service = $this->createMock('Sonata\BlockBundle\Block\Service\AbstractBlockService');
        $service->expects($this->once())->method('configureSettings');

        $blockLoader = $this->createMock('Sonata\BlockBundle\Block\BlockLoaderInterface');

        $serviceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $serviceManager->expects($this->once())->method('get')->will($this->returnValue($service));

        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->once())->method('getSettings')->will($this->returnValue([]));

        $blocksCache = [
            'by_class' => [ClassUtils::getClass($block) => 'my_cache.service.id'],
        ];

        $manager = new BlockContextManager($blockLoader, $serviceManager, $blocksCache);

        $settings = ['ttl' => 1, 'template' => 'custom.html.twig'];

        $blockContext = $manager->get($block, $settings);

        $this->assertInstanceOf('Sonata\BlockBundle\Block\BlockContextInterface', $blockContext);

        $this->assertEquals([
            'use_cache' => true,
            'extra_cache_keys' => [
                BlockContextManager::CACHE_KEY => [
                    'template' => 'custom.html.twig',
                ],
            ],
            'attr' => [],
            'template' => 'custom.html.twig',
            'ttl' => 1,
        ], $blockContext->getSettings());
    }

    public function testWithInvalidSettings()
    {
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->exactly(1))->method('error');

        $service = $this->createMock('Sonata\BlockBundle\Block\Service\AbstractBlockService');
        $service->expects($this->exactly(2))->method('configureSettings');

        $blockLoader = $this->createMock('Sonata\BlockBundle\Block\BlockLoaderInterface');

        $serviceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $serviceManager->expects($this->exactly(2))->method('get')->will($this->returnValue($service));

        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->once())->method('getSettings')->will($this->returnValue([
            'template' => [],
        ]));

        $manager = new BlockContextManager($blockLoader, $serviceManager, [], $logger);

        $blockContext = $manager->get($block);

        $this->assertInstanceOf('Sonata\BlockBundle\Block\BlockContextInterface', $blockContext);
    }

    //    @TODO: Think if the BlockContextManager should throw an exception if the resolver throw an exception
//    /**
//     * @expectedException \Sonata\BlockBundle\Exception\BlockOptionsException
//     */
//    public function testGetWithException()
//    {
//        $service = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
//        $service->expects($this->exactly(2))->method('setDefaultSettings');
//
//        $blockLoader = $this->createMock('Sonata\BlockBundle\Block\BlockLoaderInterface');
//
//        $serviceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
//        $serviceManager->expects($this->exactly(2))->method('get')->will($this->returnValue($service));
//
//        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
//        $block->expects($this->once())->method('getSettings')->will($this->returnValue(array(
//            'template' => array()
//        )));
//
//        $manager = new BlockContextManager($blockLoader, $serviceManager);
//
//        $blockContext = $manager->get($block);
//
//        $this->assertInstanceOf('Sonata\BlockBundle\Block\BlockContextInterface', $blockContext);
//    }
}
