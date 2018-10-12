<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Util\StringUtil;

class StringUtilTest extends TestCase
{
    /**
     * @dataProvider fqcnToBlockNameProvider
     */
    public function testFqcnToBlockName($fqcn, $expectedBlockName)
    {
        $blockName = StringUtil::fqcnToBlockName($fqcn);
        $this->assertSame($expectedBlockName, $blockName);
    }

    public function fqcnToBlockNameProvider()
    {
        return [
            ['SERVICE', 'service'],
            ['\Service', 'service'],
            ['\UserService', 'user'],
            ['UserService', 'user'],
            ['Vendor\Name\Space\Service', 'service'],
            ['Vendor\Name\Space\UserBlock', 'user_block'],
            ['Vendor\Name\Space\UserService', 'user'],
            ['Vendor\Name\Space\userservice', 'user'],
            ['Symfony\Component\Block\Block', 'block'],
            ['Vendor\Name\Space\BarServiceBazService', 'bar_service_baz'],
            ['FooBarBazService', 'foo_bar_baz'],
        ];
    }
}
