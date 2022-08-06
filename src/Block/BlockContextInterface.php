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

interface BlockContextInterface
{
    public function getBlock(): BlockInterface;

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array;

    public function getSetting(string $name): mixed;

    public function setSetting(string $name, mixed $value): self;

    public function getTemplate(): string;
}
