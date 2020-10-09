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

final class HttpCacheHandler implements HttpCacheHandlerInterface
{
    /**
     * @var int|null
     */
    private $currentTtl;

    public function alterResponse(Response $response): void
    {
        if (!$response->isCacheable()) {
            // the controller flags the response as private so we keep it private!
            return;
        }

        // no block has been rendered
        if (null === $this->currentTtl) {
            return;
        }

        // a block has a lower ttl that the current response, so we update the ttl to match
        // the one provided in the block
        if ($this->currentTtl < $response->getTtl()) {
            $response->setTtl($this->currentTtl);
        }
    }

    public function updateMetadata(Response $response, ?BlockContextInterface $blockContext = null): void
    {
        if (null === $this->currentTtl) {
            $this->currentTtl = $response->getTtl();
        }

        if ($response->isCacheable() && $response->getTtl() < $this->currentTtl) {
            $this->currentTtl = $response->getTtl();
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->alterResponse($event->getResponse());
    }
}
