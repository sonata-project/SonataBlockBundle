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

use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\BlockBundle\Block\Service\MenuBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Menu\MenuRegistryInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\Form\Type\ImmutableArrayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class MenuBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var MenuProviderInterface&MockObject
     */
    private $menuProvider;

    /**
     * @var MenuRegistryInterface&MockObject
     */
    private $menuRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->menuProvider = $this->createMock(MenuProviderInterface::class);
        $this->menuRegistry = $this->createMock(MenuRegistryInterface::class);
    }

    /**
     * @group legacy
     */
    public function testBuildEditForm(): void
    {
        $this->menuRegistry->expects(static::once())->method('getAliasNames')
            ->willReturn([
                'acme:demobundle:menu' => 'Test Menu',
            ]);

        $formMapper = $this->createMock(FormMapper::class);
        $block = $this->createMock(BlockInterface::class);

        $choiceOptions = [
            'required' => false,
            'label' => 'form.label_menu_name',
            'translation_domain' => 'SonataBlockBundle',
        ];

        $choiceOptions['choices'] = [
            'Test Menu' => 'acme:demobundle:menu',
        ];

        $formMapper->expects(static::once())->method('add')
            ->with('settings', ImmutableArrayType::class, [
                'keys' => [
                    ['title', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_title',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                    ['cache_policy', ChoiceType::class, [
                        'label' => 'form.label_cache_policy',
                        'translation_domain' => 'SonataBlockBundle',
                        'choices' => ['public', 'private'],
                    ]],
                    ['safe_labels', CheckboxType::class, [
                        'required' => false,
                        'label' => 'form.label_safe_labels',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                    ['current_class', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_current_class',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                    ['first_class', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_first_class',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                    ['last_class', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_last_class',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                    ['menu_template', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_menu_template',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                    ['menu_name', ChoiceType::class, $choiceOptions],
                    ['menu_class', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_menu_class',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                    ['children_class', TextType::class, [
                        'required' => false,
                        'label' => 'form.label_children_class',
                        'translation_domain' => 'SonataBlockBundle',
                    ]],
                ],
                'translation_domain' => 'SonataBlockBundle',
            ]);

        $blockService = new MenuBlockService($this->twig, $this->menuProvider, $this->menuRegistry);
        $blockService->configureEditForm($formMapper, $block);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new MenuBlockService($this->twig, $this->menuProvider, $this->menuRegistry);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'title' => '',
            'cache_policy' => 'public',
            'template' => '@SonataBlock/Block/block_core_menu.html.twig',
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
