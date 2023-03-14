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

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-extends \RecursiveIteratorIterator<RecursiveBlockIterator>
 */
final class RecursiveBlockIteratorIterator extends \RecursiveIteratorIterator
{
    /**
     * @param array<mixed>|ReadableCollection<int, mixed> $array
     */
    public function __construct($array)
    {
        parent::__construct(new RecursiveBlockIterator($array), \RecursiveIteratorIterator::SELF_FIRST);
    }
}
