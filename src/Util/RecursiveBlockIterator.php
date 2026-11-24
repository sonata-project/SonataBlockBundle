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

namespace Sonata\BlockBundle\Util;

use Doctrine\Common\Collections\ReadableCollection;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-extends \RecursiveArrayIterator<int, BlockInterface>
 */
final class RecursiveBlockIterator extends \RecursiveArrayIterator
{
    /**
     * Object ids of the parent blocks of the current level, used to detect cycles.
     *
     * @var list<int>
     */
    private array $ancestors = [];

    /**
     * @param ReadableCollection<int, BlockInterface>|array<BlockInterface> $array
     */
    public function __construct($array)
    {
        if ($array instanceof ReadableCollection) {
            $array = $array->toArray();
        }

        parent::__construct($array);
    }

    /**
     * @phpstan-return RecursiveBlockIterator
     */
    public function getChildren(): self
    {
        $current = $this->current();
        \assert(null !== $current);

        $children = new self($current->getChildren());
        $children->ancestors = [...$this->ancestors, spl_object_id($current)];

        return $children;
    }

    public function hasChildren(): bool
    {
        $current = $this->current();
        \assert(null !== $current);

        // do not descend into a block that is one of its own ancestors, it would never end
        return $current->hasChild() && !\in_array(spl_object_id($current), $this->ancestors, true);
    }
}
