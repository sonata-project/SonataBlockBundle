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
 * Facebook send button integration.
 *
 * @see https://developers.facebook.com/docs/plugins/send-button/
 *
 * @author Sylvain Deloux <sylvain.deloux@fullsix.com>
 */
class FacebookSendButtonBlockService extends BaseFacebookSocialPluginsBlockService
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'include_sdk' => true,
            'template'    => 'SonataBlockBundle:Block:block_facebook_send_button.html.twig',
            'url'         => null,
            'width'       => null,
            'height'      => null,
            'colorscheme' => $this->colorschemeList['light'],
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
        return 'Facebook Social Plugin - Send button';
    }
}
