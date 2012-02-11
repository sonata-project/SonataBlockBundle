<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use Sonata\BlockBundle\Block\BlockServiceManager;

class BlockServiceManagerTest extends \PHPUnit_Framework_TestCase
{

    public function getManager()
    {
        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');

        return new BlockServiceManager(true, $logger);
    }


    public function testgetBlockService()
    {
        $manager = $this->getManager();

        $block = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->any())->method('getType')->will($this->returnValue('test'));


        $service = $this->getMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $service->expects($this->any())->method('setManager')->will($this->returnValue(null));

        $manager->addBlockService('test', $service);

        $this->assertInstanceOf(get_class($service), $manager->getBlockService($block));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testgetBlockServiceException()
    {
        $manager = $this->getManager();

        $block = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');
        $block->expects($this->any())->method('getType')->will($this->returnValue('fakse'));

        $manager->getBlockService($block);
    }
}