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

use Sonata\BlockBundle\Exception\BlockNotFoundException;
use Sonata\BlockBundle\Model\BlockInterface;

interface BlockContextManagerInterface
{
    /**
     * Add settings for a block service.
     *
     * @param string               $type     block service
     * @param array<string, mixed> $settings
     * @param bool                 $replace  replace existing settings
     */
    public function addSettingsByType(string $type, array $settings, bool $replace = false): void;

    /**
     * Add settings for a block class.
     *
     * @param string               $class    block class
     * @param array<string, mixed> $settings
     * @param bool                 $replace  replace existing settings
     *
     * @phpstan-param class-string $class
     */
    public function addSettingsByClass(string $class, array $settings, bool $replace = false): void;

    /**
     * @param string|array<string, mixed>|BlockInterface $meta     Data send to the loader to load a block, can be anything...
     * @param array<string, mixed>                       $settings
     *
     * @throws BlockNotFoundException
     */
    public function get($meta, array $settings = []): BlockContextInterface;

    public function exists(string $type): bool;
}
