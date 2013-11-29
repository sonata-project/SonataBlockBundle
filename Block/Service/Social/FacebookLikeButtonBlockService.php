<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block\Service\Social;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Facebook like button integration.
 *
 * @see https://developers.facebook.com/docs/plugins/like-button/
 *
 * @author Sylvain Deloux <sylvain.deloux@fullsix.com>
 */
class FacebookLikeButtonBlockService extends BaseFacebookSocialPluginsBlockService
{
    protected $layoutList = array(
        'standard'     => 'standard',
        'box_count'    => 'box_count',
        'button_count' => 'button_count',
        'button'       => 'button',
    );

    protected $actionTypes = array(
        'like'      => 'like',
        'recommend' => 'recommend',
    );

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'include_sdk' => true,
            'template'    => 'SonataBlockBundle:Block:block_facebook_like_button.html.twig',
            'url'         => null,
            'width'       => null,
            'show_faces'  => true,
            'share'       => true,
            'layout'      => $this->layoutList['standard'],
            'colorscheme' => $this->colorschemeList['light'],
            'action'      => $this->actionTypes['like'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('include_sdk', 'checkbok', array('required' => false)),
                array('url',         'url',      array('required' => false)),
                array('width',       'integer',  array('required' => false)),
                array('show_faces',  'checkbox', array('required' => false)),
                array('share',       'checkbox', array('required' => false)),
                array('layout',      'choice',   array('required' => true, 'choices' => $this->layoutList)),
                array('colorscheme', 'choice',   array('required' => true, 'choices' => $this->colorschemeList)),
                array('action',      'choice',   array('required' => true, 'choices' => $this->actionTypes)),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Facebook Social Plugin - Like button';
    }
}
