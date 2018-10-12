<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Util;

class StringUtil
{
    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Converts a fully-qualified class name to a block name.
     *
     * @param string $fqcn The fully-qualified class name
     *
     * @return string|null The block name or null if not a valid FQCN
     */
    public static function fqcnToBlockName($fqcn)
    {
        // Non-greedy ("+?") to match "service" suffix, if present
        if (preg_match('~([^\\\\]+?)(service)?$~i', $fqcn, $matches)) {
            return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'], ['\\1_\\2', '\\1_\\2'], $matches[1]));
        }
    }
}
