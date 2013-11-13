<?php
/*
 * This file is part of the Sonata package.
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
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class MenuBlockService
 *
 * @package Sonata\BlockBundle\Block\Service
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class MenuBlockService extends BaseBlockService
{
    /**
     * @var MenuProviderInterface
     */
    protected $menuProvider;

    /**
     * Constructor
     *
     * @param string                $name
     * @param EngineInterface       $templating
     * @param MenuProviderInterface $menuProvider
     */
    public function __construct($name, EngineInterface $templating, MenuProviderInterface $menuProvider)
    {
        parent::__construct($name, $templating);

        $this->menuProvider = $menuProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderPrivateResponse($blockContext->getTemplate(), array(
            'menu'         => $this->getMenu($blockContext->getSettings()),
            'menu_options' => $this->getMenuOptions($blockContext->getSettings()),
            'block'        => $blockContext->getBlock(),
            'context'      => $blockContext
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $form->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array('required' => false)),
                array('menu_name', 'string', array('required' => false)),
                array('menu_class'), 'string', array('required' => false),
                array('current_class', 'string', array('required' => false)),
                array('first_class', 'string', array('required' => false)),
                array('last_class', 'string', array('required' => false)),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        if (($name = $block->getSetting('menu_name')) && $name !== "" && !$this->menuProvider->has($name)) {
            // If we specified a menu_name, check that it exists
            $errorElement->with('menu_name')
                ->addViolation('soanta.block.menu.not_existing', array('name' => $name))
            ->end();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'title'         => $this->getName(),
            'template'      => 'SonataBlockBundle:Block:block_core_menu.html.twig',
            'menu_name'     => "",
            'menu_class'    => "nav nav-list",
            'current_class' => 'active',
            'first_class'   => false,
            'last_class'    => false,
            'current_uri'   => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Menu';
    }

    /**
     * Gets the menu to render
     *
     * @param BlockContextInterface $blockContext
     *
     * @return ItemInterface|string
     */
    protected function getMenu(array $settings)
    {
        return $settings['menu_name'];
    }

    /**
     * Replaces setting keys with knp menu item options keys
     *
     * @param array $settings
     */
    protected function getMenuOptions(array $settings)
    {
        $mapping = array(
            'current_class' => 'currentClass',
            'first_class'   => 'firstClass',
            'last_class'    => 'lastClass'
        );

        $options = array();

        foreach ($settings as $key => $value) {
            if (array_key_exists($key, $mapping)) {
                $options[$mapping[$key]] = $value;
            }
        }

        return $options;
    }
}