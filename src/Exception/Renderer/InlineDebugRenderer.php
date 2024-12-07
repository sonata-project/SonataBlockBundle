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
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * This renderer uses a template to display an error message at the block position with extensive debug information.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class InlineDebugRenderer implements RendererInterface
{
    public function __construct(
        private Environment $twig,
        private string $template,
        private bool $debug,
        private bool $forceStyle = true,
    ) {
    }

    public function render(\Exception $exception, BlockInterface $block, ?Response $response = null): Response
    {
        $response ??= new Response();

        // enforce debug mode or ignore silently
        if (!$this->debug) {
            return $response;
        }

        $flattenException = FlattenException::create($exception);
        $code = $flattenException->getStatusCode();

        $parameters = [
            'exception' => $flattenException,
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code] ?? '',
            'logger' => false,
            'currentContent' => false,
            'block' => $block,
            'forceStyle' => $this->forceStyle,
        ];

        $content = $this->twig->render($this->template, $parameters);
        $response->setContent($content);

        return $response;
    }
}
