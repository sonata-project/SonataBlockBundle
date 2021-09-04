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

namespace Sonata\BlockBundle\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Twig\GlobalVariables;

final class GlobalVariablesTest extends TestCase
{
    public function testGlobalVariables(): void
    {
        $variables = new GlobalVariables([]);

        static::assertEmpty($variables->getTemplates());
    }
}
