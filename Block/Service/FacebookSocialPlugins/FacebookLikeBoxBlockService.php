<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block\Service\FacebookSocialPlugins;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Facebook like box integration.
 *
 * @see https://developers.facebook.com/docs/plugins/like-box-for-pages/
 *
 * @author Sylvain Deloux <sylvain.deloux@fullsix.com>
 */
class FacebookLikeBoxBlockService extends BaseFacebookSocialPluginsBlockService
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'include_sdk' => true,
            'template'    => 'SonataBlockBundle:Block:block_facebook_like_box.html.twig',
            'url'         => null,
            'width'       => null,
            'height'      => null,
            'colorscheme' => $this->colorschemeList['light'],
            'show_faces'  => true,
            'show_header' => true,
            'show_posts'  => false,
            'show_border' => true,
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
                array('height',      'integer',  array('required' => false)),
                array('colorscheme', 'choice',   array('required' => true, 'choices' => $this->colorschemeList)),
                array('show_faces',  'checkbox', array('required' => false)),
                array('show_header', 'checkbox', array('required' => false)),
                array('show_posts',  'checkbox', array('required' => false)),
                array('show_border', 'checkbox', array('required' => false)),
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
        return 'Facebook Social Plugin - Like box';
    }
}
