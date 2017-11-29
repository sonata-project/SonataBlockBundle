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
use Symfony\Component\Form\FormTypeInterface;

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
            ->will($this->returnValue([
                'acme:demobundle:menu' => 'Test Menu',
            ]));

        $formMapper = $this->getMockBuilder('Sonata\AdminBundle\Form\FormMapper')->disableOriginalConstructor()->getMock();
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $choiceOptions = [
            'required' => false,
        ];

        // choice_as_value options is not needed in SF 3.0+
        if (method_exists(FormTypeInterface::class, 'setDefaultOptions')) {
            $choiceOptions['choices_as_values'] = true;
        }

        $choiceOptions['choices'] = [
            'Test Menu' => 'acme:demobundle:menu',
        ];

        $formMapper->expects($this->once())->method('add')
            ->with('settings', 'sonata_type_immutable_array', [
                'keys' => [
                    ['title', 'text', ['required' => false]],
                    ['cache_policy', 'choice', ['choices' => ['public', 'private']]],
                    ['menu_name', 'choice', $choiceOptions],
                    ['safe_labels', 'checkbox', ['required' => false]],
                    ['current_class', 'text', ['required' => false]],
                    ['first_class', 'text', ['required' => false]],
                    ['last_class', 'text', ['required' => false]],
                    ['menu_class', 'text', ['required' => false]],
                    ['children_class', 'text', ['required' => false]],
                    ['menu_template', 'text', ['required' => false]],
                ],
            ]);

        $blockService = new MenuBlockService('sonata.page.block.menu', $this->templating, $this->menuProvider, $this->menuRegistry);
        $blockService->buildEditForm($formMapper, $block);
    }

    public function testDefaultSettings()
    {
        $blockService = new MenuBlockService('sonata.page.block.menu', $this->templating, $this->menuProvider, $this->menuRegistry);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
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
        ], $blockContext);
    }
}
