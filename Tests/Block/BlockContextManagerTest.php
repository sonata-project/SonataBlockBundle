<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Block;

use Sonata\BlockBundle\Block\BlockContextManager;
use Symfony\Component\Security\Core\Util\ClassUtils;

class BlockContextManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetWithValidData()
    {
        $service = $this->getMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $service->expects($this->once())->method('setDefaultSettings');

        $blockLoader = $this->getMock('Sonata\BlockBundle\Block\BlockLoaderInterface');

        $serviceManager = $this->getMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $serviceManager->expects($this->once())->method('get')->will($this->returnValue($service));

        $block = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->once())->method('getSettings')->will($this->returnValue(array()));

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $blockContext = $manager->get($block);

        $this->assertInstanceOf('Sonata\BlockBundle\Block\BlockContextInterface', $blockContext);

        $this->assertEquals(array(
            'use_cache' => true,
            'extra_cache_keys' => array(),
            'attr' => array(),
            'template' => false,
            'ttl' => 0
        ), $blockContext->getSettings());
    }

    public function testGetWithSettings()
    {
        $service = $this->getMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $service->expects($this->once())->method('setDefaultSettings');

        $blockLoader = $this->getMock('Sonata\BlockBundle\Block\BlockLoaderInterface');

        $serviceManager = $this->getMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $serviceManager->expects($this->once())->method('get')->will($this->returnValue($service));

        $block = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->once())->method('getSettings')->will($this->returnValue(array()));

        $blocksCache = array(
            'by_class' => array(\Doctrine\Common\Util\ClassUtils::getClass($block) => 'my_cache.service.id')
        );

        $manager = new BlockContextManager($blockLoader, $serviceManager, $blocksCache);

        $settings = array('ttl' => 1, 'template' => 'custom.html.twig');

        $blockContext = $manager->get($block, $settings);

        $this->assertInstanceOf('Sonata\BlockBundle\Block\BlockContextInterface', $blockContext);

        $this->assertEquals(array(
            'use_cache' => true,
            'extra_cache_keys' => array(
                BlockContextManager::CACHE_KEY => array(
                    'template' => 'custom.html.twig',
                ),
            ),
            'attr' => array(),
            'template' => 'custom.html.twig',
            'ttl' => 1
        ), $blockContext->getSettings());
    }

    /**
     * @expectedException Sonata\BlockBundle\Exception\BlockOptionsException
     */
    public function testGetWithException()
    {
        $service = $this->getMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $service->expects($this->once())->method('setDefaultSettings');

        $blockLoader = $this->getMock('Sonata\BlockBundle\Block\BlockLoaderInterface');

        $serviceManager = $this->getMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $serviceManager->expects($this->once())->method('get')->will($this->returnValue($service));

        $block = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->once())->method('getSettings')->will($this->returnValue(array(
            'template' => array()
        )));

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $blockContext = $manager->get($block);

        $this->assertInstanceOf('Sonata\BlockBundle\Block\BlockContextInterface', $blockContext);
    }
}
