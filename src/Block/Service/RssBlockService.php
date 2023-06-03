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
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RssBlockService extends AbstractBlockService implements EditableBlockService
{
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'url' => false,
            'title' => null,
            'translation_domain' => null,
            'icon' => 'fa fa-rss-square',
            'class' => null,
            'template' => '@SonataBlock/Block/block_core_rss.html.twig',
        ]);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['url', UrlType::class, [
                    'required' => false,
                    'label' => 'form.label_url',
                    'translation_domain' => 'SonataBlockBundle',
                ]],
                ['title', TextType::class, [
                    'label' => 'form.label_title',
                    'translation_domain' => 'SonataBlockBundle',
                    'required' => false,
                ]],
                ['translation_domain', TextType::class, [
                    'label' => 'form.label_translation_domain',
                    'translation_domain' => 'SonataBlockBundle',
                    'required' => false,
                ]],
                ['icon', TextType::class, [
                    'label' => 'form.label_icon',
                    'translation_domain' => 'SonataBlockBundle',
                    'required' => false,
                ]],
                ['class', TextType::class, [
                    'label' => 'form.label_class',
                    'translation_domain' => 'SonataBlockBundle',
                    'required' => false,
                ]],
            ],
            'translation_domain' => 'SonataBlockBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $errorElement
            ->with('settings[url]')
                ->addConstraint(new NotNull())
                ->addConstraint(new NotBlank())
            ->end()
            ->with('settings[title]')
                ->addConstraint(new NotNull())
                ->addConstraint(new NotBlank())
                ->addConstraint(new Length(['max' => 50]))
            ->end();
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        // merge settings
        $settings = $blockContext->getSettings();

        $feeds = false;
        if (\is_string($settings['url'])) {
            $options = [
                'http' => [
                    'user_agent' => 'Sonata/RSS Reader',
                    'timeout' => 2,
                ],
            ];

            // retrieve contents with a specific stream context to avoid php errors
            $content = @file_get_contents($settings['url'], false, stream_context_create($options));

            if (false !== $content && '' !== $content) {
                // generate a simple xml element
                try {
                    $feeds = new \SimpleXMLElement($content);
                    $feeds = $feeds->channel->item;
                } catch (\Exception) {
                    // silently fail error
                }
            }
        }

        $template = $blockContext->getTemplate();

        return $this->renderResponse($template, [
            'feeds' => $feeds,
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
        ], $response);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.block.service.rss', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-rss-square',
        ]);
    }
}
