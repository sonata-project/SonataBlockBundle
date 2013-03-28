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

use Sonata\BlockBundle\Block\BlockExecutionContextInterface;
use Symfony\Component\HttpFoundation\Response;

interface BlockRendererInterface
{
    /**
     * @param BlockExecutionContextInterface $name
     * @param null|Response                  $response
     *
     * @return Response
     */
    public function render(BlockExecutionContextInterface $name, Response $response = null);
}
