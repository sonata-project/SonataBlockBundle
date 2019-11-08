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

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Form\Type\ContainerTemplateType;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Render children pages.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ContainerBlockService extends AbstractBlockService implements EditableBlockService
{
    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $form->add('enabled');

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['code', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_code',
                ]],
                ['layout', TextareaType::class, [
                    'label' => 'form.label_layout',
                ]],
                ['class', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_class',
                ]],
                ['template', ContainerTemplateType::class, [
                    'label' => 'form.label_template',
                ]],
            ],
            'translation_domain' => 'SonataBlockBundle',
        ]);

        $form->add('children', CollectionType::class);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'decorator' => $this->getDecorator($blockContext->getSetting('layout')),
            'settings' => $blockContext->getSettings(),
        ], $response);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'code' => '',
            'layout' => '{{ CONTENT }}',
            'class' => '',
            'template' => '@SonataBlock/Block/block_container.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.block.service.container', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-square-o',
        ]);
    }

    /**
     * Returns a decorator object/array from the container layout setting.
     */
    private function getDecorator(string $layout): array
    {
        $key = '{{ CONTENT }}';
        if (false === strpos($layout, $key)) {
            return [];
        }

        $segments = explode($key, $layout);
        $decorator = [
            'pre' => $segments[0] ?? '',
            'post' => $segments[1] ?? '',
        ];

        return $decorator;
    }
}
