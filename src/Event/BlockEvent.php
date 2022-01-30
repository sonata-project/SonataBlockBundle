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

final class BlockEvent extends Event
{
    /**
     * @var array<string, mixed>
     */
    private $settings;

    /**
     * @var BlockInterface[]
     */
    private $blocks = [];

    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    public function addBlock(BlockInterface $block): void
    {
        $this->blocks[] = $block;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return BlockInterface[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getSetting(string $name, $default = null)
    {
        return $this->settings[$name] ?? $default;
    }
}
