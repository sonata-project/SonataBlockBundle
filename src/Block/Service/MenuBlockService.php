<?php

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
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Menu\MenuRegistry;
use Sonata\BlockBundle\Menu\MenuRegistryInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class MenuBlockService extends AbstractAdminBlockService
{
    /**
     * @var MenuProviderInterface
     */
    protected $menuProvider;

    /**
     * NEXT_MAJOR: remove property.
     *
     * @var array
     *
     * @deprecated since 3.3, to be removed in 4.0
     */
    protected $menus;

    /**
     * @var MenuRegistryInterface
     */
    protected $menuRegistry;

    /**
     * @param string                     $name
     * @param EngineInterface            $templating
     * @param MenuProviderInterface      $menuProvider
     * @param MenuRegistryInterface|null $menuRegistry
     */
    public function __construct($name, EngineInterface $templating, MenuProviderInterface $menuProvider, $menuRegistry = null)
    {
        parent::__construct($name, $templating);

        $this->menuProvider = $menuProvider;

        if ($menuRegistry instanceof MenuRegistryInterface) {
            $this->menuRegistry = $menuRegistry;
        } elseif (is_null($menuRegistry)) {
            $this->menuRegistry = new MenuRegistry();
        } elseif (is_array($menuRegistry)) { //NEXT_MAJOR: Remove this case
            @trigger_error(
                'Initializing '.__CLASS__.' with an array parameter is deprecated since 3.3 and will be removed in 4.0.',
                E_USER_DEPRECATED
            );
            $this->menuRegistry = new MenuRegistry();
            foreach ($menuRegistry as $menu) {
                $this->menuRegistry->add($menu);
            }
        } else {
            throw new \InvalidArgumentException(sprintf(
                'MenuRegistry must be either null or instance of %s',
                MenuRegistryInterface::class
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $responseSettings = [
            'menu' => $this->getMenu($blockContext),
            'menu_options' => $this->getMenuOptions($blockContext->getSettings()),
            'block' => $blockContext->getBlock(),
            'context' => $blockContext,
        ];

        if ('private' === $blockContext->getSetting('cache_policy')) {
            return $this->renderPrivateResponse($blockContext->getTemplate(), $responseSettings, $response);
        }

        return $this->renderResponse($blockContext->getTemplate(), $responseSettings, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $form->add('settings', 'sonata_type_immutable_array', [
            'keys' => $this->getFormSettingsKeys(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        if (($name = $block->getSetting('menu_name')) && '' !== $name && !$this->menuProvider->has($name)) {
            // If we specified a menu_name, check that it exists
            $errorElement->with('menu_name')
                ->addViolation('sonata.block.menu.not_existing', ['name' => $name])
            ->end();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'title' => $this->getName(),
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
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-bars',
        ]);
    }

    /**
     * @return array
     */
    protected function getFormSettingsKeys()
    {
        $choiceOptions = [
            'required' => false,
        ];

        $choices = $this->menuRegistry->getAliasNames();

        $choices = array_flip($choices);

        $choiceOptions['choices'] = $choices;

        return [
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
        ];
    }

    /**
     * Gets the menu to render.
     *
     * @param BlockContextInterface $blockContext
     *
     * @return ItemInterface|string
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $settings = $blockContext->getSettings();

        return $settings['menu_name'];
    }

    /**
     * Replaces setting keys with knp menu item options keys.
     *
     * @param array $settings
     *
     * @return array
     */
    protected function getMenuOptions(array $settings)
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
            if (array_key_exists($key, $mapping) && null !== $value) {
                $options[$mapping[$key]] = $value;
            }
        }

        return $options;
    }
}
