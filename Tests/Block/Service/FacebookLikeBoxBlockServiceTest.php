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

use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Block\Service\FacebookSocialPlugins\FacebookLikeBoxBlockService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sonata\BlockBundle\Block\BlockContext;

class FacebookLikeBoxBlockServiceTest extends BaseTestBlockService
{
    public function testService()
    {
        $templating = new FakeTemplating;
        $service    = new FacebookLikeBoxBlockService('sonata.block.service.facebook.like_box', $templating);

        $block = new Block;
        $block->setType('core.text');
        $block->setSettings(array(
            'url'         => 'url_setting',
            'width'       => 'width_setting',
            'height'      => 'height_setting',
            'colorscheme' => 'colorscheme_setting',
            'show_faces'  => 'show_faces_setting',
            'show_header' => 'show_header_setting',
            'show_posts'  => 'show_posts_setting',
            'show_border' => 'show_border_setting',
        ));


        $optionResolver = new OptionsResolver();
        $service->setDefaultSettings($optionResolver);

        $blockContext = new BlockContext($block, $optionResolver->resolve($block->getSettings()));

        $formMapper = $this->getMock('Sonata\\AdminBundle\\Form\\FormMapper', array(), array(), '', false);
        $formMapper->expects($this->exactly(2))->method('add');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);

        $service->execute($blockContext);

        $this->assertEquals('url_setting',         $templating->parameters['settings']['url']);
        $this->assertEquals('width_setting',       $templating->parameters['settings']['width']);
        $this->assertEquals('height_setting',      $templating->parameters['settings']['height']);
        $this->assertEquals('colorscheme_setting', $templating->parameters['settings']['colorscheme']);
        $this->assertEquals('show_faces_setting',  $templating->parameters['settings']['show_faces']);
        $this->assertEquals('show_header_setting', $templating->parameters['settings']['show_header']);
        $this->assertEquals('show_posts_setting',  $templating->parameters['settings']['show_posts']);
        $this->assertEquals('show_border_setting', $templating->parameters['settings']['show_border']);
    }
}
