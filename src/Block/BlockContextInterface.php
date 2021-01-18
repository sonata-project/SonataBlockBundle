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

    public function getSettings(): array;

    /**
     * @return mixed
     */
    public function getSetting(string $name);

    /**
     * @param mixed $value
     */
    public function setSetting(string $name, $value): self;

    public function getTemplate(): ?string;
}
