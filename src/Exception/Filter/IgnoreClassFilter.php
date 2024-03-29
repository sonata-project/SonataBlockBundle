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

namespace Sonata\BlockBundle\Exception\Filter;

use Sonata\BlockBundle\Model\BlockInterface;

/**
 * This filter ignores exceptions that inherit a given class or interface, or in other words, it will only handle
 * exceptions that do not inherit the given class or interface.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class IgnoreClassFilter implements FilterInterface
{
    public function __construct(private string $class)
    {
    }

    public function handle(\Throwable $exception, BlockInterface $block): bool
    {
        return !$exception instanceof $this->class;
    }
}
