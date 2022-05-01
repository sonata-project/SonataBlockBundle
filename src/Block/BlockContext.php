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
    private BlockInterface $block;

    /**
     * @var array<string, mixed>
     */
    private array $settings;

    /**
     * @param array<string, mixed> $settings
     */
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
            throw new \RuntimeException(sprintf('Unable to find the option `%s` (%s) - define the option in the related BlockServiceInterface', $name, $this->block->getType() ?? ''));
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

    /**
     * NEXT_MAJOR: Restrict typehint to string.
     */
    public function getTemplate(): ?string
    {
        $template = $this->getSetting('template');

        if (!\is_string($template)) {
            @trigger_error(
                'Not providing a string value for the "template" setting is deprecated since'
                .' sonata-project/block-bundle 4.10 and will be throw an exception in version 5.0.',
                \E_USER_DEPRECATED
            );

            // NEXT_MAJOR: Uncomment the exception instead.
            // throw new \InvalidArgumentException('The "template" setting MUST be a string.');
        }

        return $template;
    }
}
