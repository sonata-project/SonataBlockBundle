<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
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
        return [
            new \Twig_SimpleFunction('sonata_block_exists',
                [$this->blockHelper, 'exists']
            ),
            new \Twig_SimpleFunction('sonata_block_render',
                [$this->blockHelper, 'render'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction('sonata_block_render_event',
                [$this->blockHelper, 'renderEvent'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction('sonata_block_include_javascripts',
                [$this->blockHelper, 'includeJavascripts'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction('sonata_block_include_stylesheets',
                [$this->blockHelper, 'includeStylesheets'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_block';
    }
}
