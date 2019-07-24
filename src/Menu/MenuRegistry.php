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

namespace Sonata\BlockBundle\Menu;

/**
 * @author Christian Gripp <mail@core23.de>
 */
final class MenuRegistry implements MenuRegistryInterface
{
    /**
     * @var string[]
     */
    private $names = [];

    /**
     * {@inheritdoc}
     */
    public function add(string $menu): void
    {
        $this->names[$menu] = $menu;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNames(): array
    {
        return $this->names;
    }
}
