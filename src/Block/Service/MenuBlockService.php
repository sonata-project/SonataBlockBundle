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

use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Menu\MenuRegistryInterface;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Hugo Briand <briand@ekino.com>
 *
 * @final since sonata-project/block-bundle 4.18
 */
class MenuBlockService extends AbstractMenuBlockService implements EditableBlockService
{
    public function __construct(
        Environment $twig,
        private MenuProviderInterface $menuProvider,
        private MenuRegistryInterface $menuRegistry
    ) {
        parent::__construct($twig);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $name = $block->getSetting('menu_name');
        if (null !== $name && '' !== $name && !$this->menuProvider->has($name)) {
            // If we specified a menu_name, check that it exists
            $errorElement->with('menu_name')
                ->addViolation('sonata.block.menu.not_existing', ['%name%' => $name])
            ->end();
        }
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults([
            'menu_name' => '',
            // NEXT_MAJOR: Remove.
            'current_uri' => null,
            // NEXT_MAJOR: Remove.
            'menu_class' => 'list-group',
            // NEXT_MAJOR: Remove.
            'children_class' => 'list-group-item',
        ]);

        // NEXT_MAJOR: Remove setDeprecated.
        $resolver->setDeprecated(
            'current_uri',
            ...$this->deprecationParameters(
                '4.18',
                'Option "current_uri" is deprecated since sonata-project/block-bundle 4.18 and will be removed in 5.0.'
            )
        );

        // NEXT_MAJOR: Remove setDeprecated.
        $resolver->setDeprecated(
            'menu_class',
            ...$this->deprecationParameters(
                '4.18',
                'Option "menu_class" is deprecated since sonata-project/block-bundle 4.18 and will be removed in 5.0.'
            )
        );

        // NEXT_MAJOR: Remove setDeprecated.
        $resolver->setDeprecated(
            'children_class',
            ...$this->deprecationParameters(
                '4.18',
                'Option "children_class" is deprecated since sonata-project/block-bundle 4.18 and will be removed in 5.0.'
            )
        );
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.block.service.menu', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-bars',
        ]);
    }

    protected function getFormSettingsKeys(): array
    {
        $choiceOptions = [
            'required' => true,
            'label' => 'form.label_menu_name',
            'translation_domain' => 'SonataBlockBundle',
        ];

        $choiceOptions['choices'] = array_flip($this->menuRegistry->getAliasNames());

        return array_merge(
            parent::getFormSettingsKeys(),
            [
                ['menu_name', ChoiceType::class, $choiceOptions],
                // NEXT_MAJOR: Remove this and the related translations.
                ['menu_class', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_menu_class',
                    'translation_domain' => 'SonataBlockBundle',
                ]],
                // NEXT_MAJOR: Remove this and the related translations.
                ['children_class', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_children_class',
                    'translation_domain' => 'SonataBlockBundle',
                ]],
            ]
        );
    }

    protected function getMenu(BlockContextInterface $blockContext)
    {
        $settings = $blockContext->getSettings();

        return $settings['menu_name'];
    }

    /**
     * This class is a BC layer for deprecation messages for symfony/options-resolver < 5.1.
     * Remove this class when dropping support for symfony/options-resolver < 5.1.
     *
     * @return mixed[]
     */
    private function deprecationParameters(string $version, string $message): array
    {
        // @phpstan-ignore-next-line
        if (method_exists(OptionsResolver::class, 'define')) {
            return [
                'sonata-project/block-bundle',
                $version,
                $message,
            ];
        }

        return [$message];
    }
}
