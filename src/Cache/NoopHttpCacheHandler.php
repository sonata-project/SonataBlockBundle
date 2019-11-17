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

namespace Sonata\BlockBundle\Cache;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class NoopHttpCacheHandler implements HttpCacheHandlerInterface
{
    public function alterResponse(Response $response): void
    {
    }

    public function updateMetadata(Response $response, ?BlockContextInterface $blockContext = null): void
    {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
    }
}
