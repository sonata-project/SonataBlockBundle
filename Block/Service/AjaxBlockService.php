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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AjaxBlockService extends AbstractAdminBlockService
{
    /**
     * Colors available in AdminLTE 2.3.3 css for background with bg-xxx and bg-xxx-active.
     *
     * @var string[]
     */
    protected static $colors = array(
        'bg-red' => 'red',
        'bg-yellow' => 'yellow',
        'bg-aqua' => 'aqua',
        'bg-blue' => 'blue',
        'bg-light-blue' => 'light-blue',
        'bg-green' => 'green',
        'bg-navy' => 'navy',
        'bg-teal' => 'teal',
        'bg-olive' => 'olive',
        'bg-lime' => 'lime',
        'bg-orange' => 'orange',
        'bg-fuchsia' => 'fuchsia',
        'bg-purple' => 'purple',
        'bg-maroon' => 'maroon',
        'bg-black' => 'black',
    );

    /**
     * Each template corresponds to the AdminLTE 2.3.3 widgets.
     *
     * @var string[]
     */
    protected static $templates = array(
        'SonataBlockBundle:Block:block_ajax_simple.html.twig' => 'simple',
        'SonataBlockBundle:Block:block_ajax_progress.html.twig' => 'progress',
        'SonataBlockBundle:Block:block_ajax_link.html.twig' => 'link',
    );

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getTemplate(), array(
            'block_context' => $blockContext,
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $colorChoices = self::$colors;
        $colorChoiceOptions = array(
            'required' => true,
            'label' => 'form.label_color',
            'choice_translation_domain' => false,
        );

        $templateChoices = self::$templates;
        $templateChoiceOptions = array(
            'required' => true,
            'label' => 'form.label_template',
            'choice_label' => function ($value, $key, $index) {
                return 'form.template_'.$index;
            },
        );

        // NEXT_MAJOR: remove SF 2.7+ BC
        if (method_exists('Symfony\Component\Form\AbstractType', 'configureOptions')) {
            // choice_as_value options is not needed in SF 3.0+
            if (method_exists('Symfony\Component\Form\FormTypeInterface', 'setDefaultOptions')) {
                $colorChoiceOptions['choices_as_values'] = true;
                $templateChoiceOptions['choices_as_values'] = true;
            }
            $colorChoices = array_flip($colorChoices);
            $templateChoices = array_flip($templateChoices);
        }

        $colorChoiceOptions['choices'] = $colorChoices;
        $templateChoiceOptions['choices'] = $templateChoices;

        // NEXT_MAJOR: Remove this line when drop Symfony <2.8 support
        $arrayType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Sonata\CoreBundle\Form\Type\ImmutableArrayType' : 'sonata_type_immutable_array';
        $choiceType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType' : 'choice';
        $textType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\TextType' : 'text';
        $urlType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\UrlType' : 'url';

        $form->add('settings', $arrayType, array(
            'keys' => array(
                array('text', $textType, array(
                    'required' => false,
                    'label' => 'form.label_title',
                )),
                array('class', $textType, array(
                    'required' => false,
                    'label' => 'form.label_class',
                )),
                array('icon', $textType, array(
                    'required' => false,
                    'label' => 'form.label_icon',
                )),
                array('color', $choiceType, $colorChoiceOptions),
                array('link', $urlType, array(
                     'required' => false,
                     'label' => 'form.label_link',
                )),
                array('url', $urlType, array(
                    'required' => false,
                    'label' => 'form.label_url',
                )),
                array('template', $choiceType, $templateChoiceOptions),
            ),
            'translation_domain' => 'SonataBlockBundle',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'text' => '',
            'class' => '',
            'icon' => 'fa fa-dashboard',
            'color' => 'bg-aqua',
            'url' => null,
            'link' => null,
            'template' => 'SonataBlockBundle:Block:block_ajax_simple.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (!is_null($code) ? $code : $this->getName()), false, 'SonataBlockBundle', array(
            'class' => 'fa fa-dashboard',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascripts($media)
    {
        return array(
            'bundles/sonatablock/ajax-block.js',
        );
    }
}
