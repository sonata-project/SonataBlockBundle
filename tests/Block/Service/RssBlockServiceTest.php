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

namespace Sonata\BlockBundle\Tests\Block\Service;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\Service\RssBlockService;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\BlockBundle\Util\OptionsResolver;

final class RssBlockServiceTest extends BlockServiceTestCase
{
    /**
     * only test if the API is not broken.
     */
    public function testService()
    {
        // NEXT_MAJOR: Remove the second argument
        $service = new RssBlockService($this->twig, $this->templating);

        $block = new Block();
        $block->setType('core.text');
        $block->setSettings([
            'content' => 'my text',
        ]);

        $optionResolver = new OptionsResolver();
        $service->setDefaultSettings($optionResolver);

        $blockContext = new BlockContext($block, $optionResolver->resolve());

        $formMapper = $this->createMock(FormMapper::class);
        $formMapper->expects(static::exactly(2))->method('add');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);

        $service->execute($blockContext);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @expectedDeprecation Method Sonata\BlockBundle\Block\Service\RssBlockService::getTemplating() is deprecated since sonata-project/block-bundle 3.%s and will be removed as of version 4.0.
     */
    public function testGetTemplatingDeprecation()
    {
        $service = new RssBlockService('sonata.page.block.rss', $this->templating);

        $block = new Block();
        $block->setType('core.text');
        $block->setSettings([
            'content' => 'my text',
        ]);

        $optionResolver = new OptionsResolver();
        $service->setDefaultSettings($optionResolver);

        $blockContext = new BlockContext($block, $optionResolver->resolve());

        $formMapper = $this->createMock(FormMapper::class);
        $formMapper->expects(static::exactly(2))->method('add');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);

        $service->execute($blockContext);
    }
}
