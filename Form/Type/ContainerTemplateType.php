<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\BlockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Class ContainerTemplateType
 *
 * @package Sonata\BlockBundle\Form\Type
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class ContainerTemplateType extends AbstractType
{
    /**
     * @var array
     */
    protected $templateChoices;

    /**
     * @var array
     */
    protected $bundles;

    /**
     * @param array $templateChoices
     * @param array $bundles
     */
    public function __construct(array $templateChoices, array $bundles)
    {
        $this->templateChoices = $templateChoices;
        $this->bundles         = $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_type_container_template_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $templateChoices = $this->templateChoices;
        $bundles         = $this->bundles;

        $resolver->setDefaults(array(
            'context'           => false,
            'multiple'          => false,
            'expanded'          => false,
            'choices'           => function (Options $options, $previousValue) use ($templateChoices, $bundles) {
                if (isset($bundles['SonataPageBundle']) && isset($templateChoices['SonataBlockBundle:Block:block_container.html.twig'])) {
                    unset($templateChoices['SonataBlockBundle:Block:block_container.html.twig']);
                } else if (isset($templateChoices['SonataPagekBundle:Block:block_container.html.twig'])) {
                    unset($templateChoices['SonataPageBundle:Block:block_container.html.twig']);
                }

                return $templateChoices;
            },
            'preferred_choices' => array(),
            'empty_data'        => function (Options $options) {
                    $multiple = isset($options['multiple']) && $options['multiple'];
                    $expanded = isset($options['expanded']) && $options['expanded'];

                    return $multiple || $expanded ? array() : '';
                },
            'empty_value'       => function (Options $options, $previousValue) {
                    $multiple = isset($options['multiple']) && $options['multiple'];
                    $expanded = isset($options['expanded']) && $options['expanded'];

                    return $multiple || $expanded || !isset($previousValue) ? null : '';
                },
            'error_bubbling'    => false,
        ));
    }
}