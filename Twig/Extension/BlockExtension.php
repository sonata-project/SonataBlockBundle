<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Twig\Extension;

use Sonata\BlockBundle\Templating\Helper\BlockHelper;

class BlockExtension extends \Twig_Extension
{
    /**
     * @var BlockHelper
     */
    protected $blockHelper;

    /**
     * BlockExtension constructor.
     *
     * @param BlockHelper $blockHelper
     */
    public function __construct(BlockHelper $blockHelper)
    {
        $this->blockHelper = $blockHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('sonata_block_render',
                array($this->blockHelper, 'render'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('sonata_block_render_event',
                array($this->blockHelper, 'renderEvent'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('sonata_block_include_javascripts',
                array($this->blockHelper, 'includeJavascripts'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('sonata_block_include_stylesheets',
                array($this->blockHelper, 'includeStylesheets'),
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_block';
    }
}
