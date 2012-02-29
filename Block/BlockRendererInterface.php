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
     * @param \Sonata\BlockBundle\Model\BlockInterface $name
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    function render(BlockInterface $name, Response $response = null);
}