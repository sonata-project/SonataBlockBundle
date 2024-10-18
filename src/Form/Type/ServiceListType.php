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

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @psalm-suppress MissingTemplateParam https://github.com/phpstan/phpstan-symfony/issues/320
 */
final class ServiceListType extends AbstractType
{
    public function __construct(private BlockServiceManagerInterface $manager)
    {
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_block_service_choice';
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $manager = $this->manager;

        $resolver->setRequired([
            'context',
        ]);

        $resolver->setDefaults([
            'multiple' => false,
            'expanded' => false,
            'choices' => static function (Options $options) use ($manager): array {
                $types = [];
                foreach ($manager->getServicesByContext($options['context'], $options['include_containers']) as $code => $service) {
                    if ($service instanceof EditableBlockService) {
                        $types[\sprintf('%s - %s', $service->getMetadata()->getTitle(), $code)] = $code;
                    } else {
                        $types[\sprintf('%s', $code)] = $code;
                    }
                }

                return $types;
            },
            'preferred_choices' => [],
            'empty_data' => static function (Options $options) {
                $multiple = $options['multiple'] ?? false;
                $expanded = $options['expanded'] ?? false;

                return true === $multiple || true === $expanded ? [] : '';
            },
            'empty_value' => static function (Options $options, mixed $previousValue): ?string {
                $multiple = $options['multiple'] ?? false;
                $expanded = $options['expanded'] ?? false;

                return true === $multiple || true === $expanded || !isset($previousValue) ? null : '';
            },
            'error_bubbling' => false,
            'include_containers' => false,
        ]);
    }
}
