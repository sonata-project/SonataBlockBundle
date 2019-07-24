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

interface BlockLoaderInterface
{
    /**
     * @param string|array $name
     *
     * @throws BlockNotFoundException if no block with that name is found
     */
    public function load($name): BlockInterface;

    /**
     * @param string|array $name
     */
    public function support($name): bool;
}
