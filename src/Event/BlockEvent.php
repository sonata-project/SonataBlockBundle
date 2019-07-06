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

namespace Sonata\BlockBundle\Event;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BlockEvent extends Event
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var BlockInterface[]
     */
    protected $blocks = [];

    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * @param BlockInterface $block
     */
    public function addBlock(BlockInterface $block): void
    {
        $this->blocks[] = $block;
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
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSetting($name, $default = null)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }
}
