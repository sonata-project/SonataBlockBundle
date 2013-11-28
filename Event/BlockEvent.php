<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Event;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\EventDispatcher\Event;

class BlockEvent extends Event
{
    protected $settings;

    protected $block;

    /**
     * @param array  $settings
     */
    public function __construct(array $settings = array())
    {
        $this->settings = $settings;
    }

    /**
     * @param BlockInterface $block
     */
    public function setBlock(BlockInterface $block)
    {
        $this->block = $block;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return mixed
     */
    public function getBlock()
    {
        return $this->block;
    }
}
