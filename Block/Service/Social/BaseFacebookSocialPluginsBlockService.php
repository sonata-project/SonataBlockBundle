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

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BaseBlockService;

/**
 * Abstract class for Facebook Social Plugins blocks services.
 *
 * @author Sylvain Deloux <sylvain.deloux@fullsix.com>
 */
abstract class BaseFacebookSocialPluginsBlockService extends BaseBlockService
{
    protected $colorschemeList = array(
        'light' => 'light',
        'dark'  => 'dark',
    );

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $blockContext->getSettings();

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block'       => $blockContext->getBlock(),
            'settings'    => $settings,
            'include_sdk' => $settings['include_sdk'],
        ), $response);
    }
}
