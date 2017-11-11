<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Block\Service;

use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\Service\AjaxBlockService;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;

class AjaxBlockServiceTest extends AbstractBlockServiceTestCase
{
    public function testDefaultSettings()
    {
        $blockService = new AjaxBlockService('sonata.block.ajax', $this->templating);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'text' => '',
            'class' => '',
            'icon' => 'fa fa-dashboard',
            'color' => 'bg-aqua',
            'url' => null,
            'link' => null,
            'template' => 'SonataBlockBundle:Block:block_ajax_simple.html.twig',
        ), $blockContext);
    }

    public function testExecute()
    {
        $block = new Block();
        $block->setName('block.name');
        $block->setType('sonata.block.ajax');
        $block->setSettings(array(
            'text' => '',
            'class' => '',
            'icon' => 'fa fa-dashboard',
            'color' => 'bg-aqua',
            'url' => null,
            'link' => null,
            'template' => 'SonataBlockBundle:Block:block_ajax_simple.html.twig',
        ));
        $blockContext = new BlockContext($block, array('template' => 'SonataBlockBundle:Block:block_ajax_simple.html.twig'));
        $blockService = new AjaxBlockService('sonata.block.ajax', $this->templating);
        $blockService->execute($blockContext);
        $this->assertSame('SonataBlockBundle:Block:block_ajax_simple.html.twig', $this->templating->view);
        $this->assertSame('block.name', $this->templating->parameters['block']->getName());
        $this->assertInstanceOf('Sonata\BlockBundle\Model\Block', $this->templating->parameters['block']);
    }
}
