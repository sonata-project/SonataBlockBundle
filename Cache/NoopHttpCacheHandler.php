<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Cache;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

class NoopHttpCacheHandler implements HttpCacheHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function alterResponse(Response $response)
    {}

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(Response $response, BlockContextInterface $blockContext = null)
    {}
}