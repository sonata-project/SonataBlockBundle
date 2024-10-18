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

namespace Sonata\BlockBundle\Exception\Renderer;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * This renderer uses a template to display an error message at the block position.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class InlineRenderer implements RendererInterface
{
    public function __construct(
        private Environment $twig,
        private string $template,
    ) {
    }

    public function render(\Exception $exception, BlockInterface $block, ?Response $response = null): Response
    {
        $parameters = [
            'exception' => $exception,
            'block' => $block,
        ];

        $content = $this->twig->render($this->template, $parameters);

        $response ??= new Response();
        $response->setContent($content);

        return $response;
    }
}
