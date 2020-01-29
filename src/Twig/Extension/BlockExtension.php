<?php

declare(strict_types=1);

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
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BlockExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'sonata_block_exists',
                [BlockHelper::class, 'exists']
            ),
            new TwigFunction(
                'sonata_block_render',
                [BlockHelper::class, 'render'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'sonata_block_render_event',
                [BlockHelper::class, 'renderEvent'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'sonata_block_include_javascripts',
                [BlockHelper::class, 'includeJavascripts'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'sonata_block_include_stylesheets',
                [BlockHelper::class, 'includeStylesheets'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
