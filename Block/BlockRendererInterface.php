<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;

interface BlockRendererInterface
{
    /**
     * @param BlockInterface $name
     * @param null|Response  $response
     *
     * @return Response
     */
    public function render(BlockInterface $name, Response $response = null);
}
