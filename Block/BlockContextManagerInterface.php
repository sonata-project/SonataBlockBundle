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

interface BlockContextManagerInterface
{
    /**
     * @param mixed $meta     Data send to the loader to load a block, can be anything...
     * @param array $settings
     *
     * @return BlockContextInterface
     */
    public function get($meta, array $settings = array());
}