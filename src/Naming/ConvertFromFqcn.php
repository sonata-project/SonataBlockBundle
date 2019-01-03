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

namespace Sonata\BlockBundle\Naming;

use Sonata\BlockBundle\Naming\Exception\NamingException;

/**
 * Converts a fully-qualified class name to a block name.
 *
 * @author Christian Gripp <mail@core23.de>
 */
final class ConvertFromFqcn
{
    /**
     * @param string $fqcn The fully-qualified class name
     *
     * @throws NamingException
     *
     * @return string The block name
     */
    public function __invoke($fqcn)
    {
        // Non-greedy ("+?") to match "service" suffix, if present
        if (preg_match('~([^\\\\]+?)(service)?$~i', $fqcn, $matches)) {
            return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], $matches[1]));
        }

        throw new NamingException(sprintf('The name "%s" is not a valid FCQN', $fqcn));
    }
}
