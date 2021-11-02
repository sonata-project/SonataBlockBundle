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

namespace Sonata\BlockBundle\Model;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\Doctrine\Model\PageableManagerInterface;

/**
 * @deprecated since sonata-project/block-bundle 4.8.0, to be removed in 5.0.
 *
 * @phpstan-template T of object
 * @phpstan-extends ManagerInterface<T>
 */
interface BlockManagerInterface extends ManagerInterface, PageableManagerInterface
{
}
