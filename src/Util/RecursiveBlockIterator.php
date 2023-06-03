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
        return new self($this->current()->getChildren());
    }

    public function hasChildren(): bool
    {
        return $this->current()->hasChild();
    }
}
