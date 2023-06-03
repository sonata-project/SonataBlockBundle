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

namespace Sonata\BlockBundle\Block\Service;

use Knp\Menu\ItemInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractMenuBlockService extends AbstractBlockService implements EditableBlockService
{
    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $template = $blockContext->getTemplate();

        $responseSettings = [
            'menu' => $this->getMenu($blockContext),
            'menu_options' => $this->getMenuOptions($blockContext->getSettings()),
            'block' => $blockContext->getBlock(),
            'context' => $blockContext,
        ];

        return $this->renderResponse($template, $responseSettings, $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $form->add('settings', ImmutableArrayType::class, [
            'keys' => $this->getFormSettingsKeys(),
            'translation_domain' => 'SonataBlockBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'title' => '',
            'template' => '@SonataBlock/Block/block_core_menu.html.twig',
            'safe_labels' => false,
            'current_class' => 'active',
            'first_class' => false,
            'last_class' => false,
            'menu_template' => null,
        ]);
    }

    /**
     * @return array<array{string, class-string<FormTypeInterface>, array<string, mixed>}>
     */
    protected function getFormSettingsKeys(): array
    {
        return [
            ['title', TextType::class, [
                'required' => false,
                'label' => 'form.label_title',
                'translation_domain' => 'SonataBlockBundle',
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
        ];
    }

    /**
     * Gets the menu to render.
     */
    abstract protected function getMenu(BlockContextInterface $blockContext): ItemInterface|string;

    /**
     * Replaces setting keys with knp menu item options keys.
     *
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    private function getMenuOptions(array $settings): array
    {
        $mapping = [
            'current_class' => 'currentClass',
            'first_class' => 'firstClass',
            'last_class' => 'lastClass',
            'safe_labels' => 'allow_safe_labels',
            'menu_template' => 'template',
        ];

        $options = [];

        foreach ($settings as $key => $value) {
            if (\array_key_exists($key, $mapping) && null !== $value) {
                $options[$mapping[$key]] = $value;
            }
        }

        return $options;
    }
}
