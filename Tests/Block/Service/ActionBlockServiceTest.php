<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Block\Service;

use Sonata\BlockBundle\Block\Service\ActionBlockService;
use Sonata\BlockBundle\Model\Block;

class ActionBlockServiceTest extends BaseTestBlockService
{
    public function testService()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface', array('forward', 'handle'));
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request', array());
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response', array('getContent'));

        $kernel->expects($this->exactly(1))->method('forward')->will($this->returnValue($response));

        $templating = new FakeTemplating;
        $service = new ActionBlockService('sonata.page.block.action', $templating, $kernel, $request);

        $block = new Block;
        $block->setType('core.action');
        $block->setSettings(array(
            'action' => 'SonataBlockBundle:Page:blockPreview'
        ));

        $formMapper = $this->getMock('Sonata\\AdminBundle\\Form\\FormMapper', array(), array(), '', false);
        $formMapper->expects($this->exactly(2))->method('add');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);

        $service->execute($block);

        $this->assertEquals('SonataBlockBundle:Page:blockPreview', $templating->parameters['block']->getSetting('action'));
    }
}