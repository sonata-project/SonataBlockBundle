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

/**
 * This renderer re-throws the exception and lets the framework handle the exception.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class MonkeyThrowRenderer implements RendererInterface
{
    public function render(\Exception $exception, BlockInterface $block, ?Response $response = null): Response
    {
        throw $exception;
    }
}
