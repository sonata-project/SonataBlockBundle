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

interface BlockLoaderInterface
{
    /**
     * @param mixed $name
     * @return BlockLoaderInterface
     */
    function load($name);

    /**
     * @param mixed $block
     * @return boolean
     */
    function support($name);
}