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

use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\BlockBundle\Block\Service\MenuBlockService;
use Sonata\BlockBundle\Menu\MenuRegistryInterface;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;

class MenuBlockServiceTest extends AbstractBlockServiceTestCase
{
    /**
     * @var MenuProviderInterface
     */
    private $menuProvider;

    /**
     * @var MenuRegistryInterface
     */
    private $menuRegistry;

    protected function setUp()
    {
        parent::setUp();

        $this->menuProvider = $this->createMock('Knp\Menu\Provider\MenuProviderInterface');
        $this->menuRegistry = $this->createMock('Sonata\BlockBundle\Menu\MenuRegistryInterface');
    }

    public function testBuildEditForm()
    {
        $this->menuRegistry->expects($this->once())->method('getAliasNames')
            ->will($this->returnValue(array(
                'acme:demobundle:menu' => 'Test Menu',
            )));

        $formMapper = $this->getMockBuilder('Sonata\AdminBundle\Form\FormMapper')->disableOriginalConstructor()->getMock();
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $choiceOptions = array(
            'required' => false,
        );

        $choices = array('Test Menu' => 'acme:demobundle:menu');

        $choiceOptions['choices'] = $choices;

        $formMapper->expects($this->once())->method('add')
            ->with('settings', 'sonata_type_immutable_array', array(
                'keys' => array(
                    array('title', 'text', array('required' => false)),
                    array('cache_policy', 'choice', array('choices' => array('public', 'private'))),
                    array('menu_name', 'choice', $choiceOptions),
                    array('safe_labels', 'checkbox', array('required' => false)),
                    array('current_class', 'text', array('required' => false)),
                    array('first_class', 'text', array('required' => false)),
                    array('last_class', 'text', array('required' => false)),
                    array('menu_class', 'text', array('required' => false)),
                    array('children_class', 'text', array('required' => false)),
                    array('menu_template', 'text', array('required' => false)),
                ),
            ));

        $blockService = new MenuBlockService('sonata.page.block.menu', $this->templating, $this->menuProvider, $this->menuRegistry);
        $blockService->buildEditForm($formMapper, $block);
    }

    public function testDefaultSettings()
    {
        $blockService = new MenuBlockService('sonata.page.block.menu', $this->templating, $this->menuProvider, $this->menuRegistry);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'title' => 'sonata.page.block.menu',
            'cache_policy' => 'public',
            'template' => 'SonataBlockBundle:Block:block_core_menu.html.twig',
            'menu_name' => '',
            'safe_labels' => false,
            'current_class' => 'active',
            'first_class' => false,
            'last_class' => false,
            'current_uri' => null,
            'menu_class' => 'list-group',
            'children_class' => 'list-group-item',
            'menu_template' => null,
        ), $blockContext);
    }
}
