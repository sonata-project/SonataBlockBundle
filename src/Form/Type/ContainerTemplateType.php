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

namespace Sonata\BlockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
final class ContainerTemplateType extends AbstractType
{
    /**
     * @var array<string, string>
     */
    private array $templateChoices;

    /**
     * @param array<string, string> $templateChoices
     */
    public function __construct(array $templateChoices)
    {
        $this->templateChoices = $templateChoices;
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_type_container_template_choice';
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->templateChoices,
        ]);
    }
}
