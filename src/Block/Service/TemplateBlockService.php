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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Twig\Environment;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class TemplateBlockService extends AbstractBlockService implements EditableBlockService
{
    /**
     * @param array<string, string> $templates Templates editors can choose from (label => template name),
     *                                         configured with "sonata_block.blocks.<id>.templates".
     *                                         When empty, any template name can be entered.
     */
    public function __construct(
        Environment $twig,
        private array $templates = [],
    ) {
        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $template = $blockContext->getTemplate();

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $templateField = ['template', null, [
            'label' => 'form.label_template',
            'translation_domain' => 'SonataBlockBundle',
        ]];

        if ([] !== $this->templates) {
            $templateField = ['template', ChoiceType::class, [
                'choices' => $this->templates,
                'label' => 'form.label_template',
                'translation_domain' => 'SonataBlockBundle',
            ]];
        }

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [$templateField],
            'translation_domain' => 'SonataBlockBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        if ([] === $this->templates) {
            return;
        }

        $errorElement
            ->with('settings[template]')
                ->addConstraint(new Choice(choices: array_values($this->templates), message: 'sonata.block.template.not_allowed'))
            ->end();
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => '@SonataBlock/Block/block_template.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.block.service.template', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-code',
        ]);
    }
}
