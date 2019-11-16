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
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * This renderer uses a template to display an error message at the block position with extensive debug information.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class InlineDebugRenderer implements RendererInterface
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $forceStyle;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig, string $template, bool $debug, bool $forceStyle = true)
    {
        $this->twig = $twig;
        $this->template = $template;
        $this->debug = $debug;
        $this->forceStyle = $forceStyle;
    }

    public function render(\Exception $exception, BlockInterface $block, ?Response $response = null): Response
    {
        $response = $response ?: new Response();

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
