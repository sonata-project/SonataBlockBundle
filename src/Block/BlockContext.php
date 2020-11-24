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

namespace Sonata\BlockBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;

final class BlockContext implements BlockContextInterface
{
    /**
     * @var BlockInterface
     */
    private $block;

    /**
     * @var array
     */
    private $settings;

    public function __construct(BlockInterface $block, array $settings = [])
    {
        $this->block = $block;
        $this->settings = $settings;
    }

    public function getBlock(): BlockInterface
    {
        return $this->block;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getSetting(string $name)
    {
        if (!\array_key_exists($name, $this->settings)) {
            throw new \RuntimeException(sprintf('Unable to find the option `%s` (%s) - define the option in the related BlockServiceInterface', $name, $this->block->getType()));
        }

        return $this->settings[$name];
    }

    public function setSetting(string $name, $value): BlockContextInterface
    {
        if (!\array_key_exists($name, $this->settings)) {
            throw new \RuntimeException(sprintf('It\'s not possible add non existing setting `%s`.', $name));
        }

        $this->settings[$name] = $value;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->getSetting('template');
    }
}
