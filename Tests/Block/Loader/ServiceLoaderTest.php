<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Block;

use Sonata\BlockBundle\Block\Loader\ServiceLoader;

class ServiceLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testBlockNotFoundException()
    {
        $loader = new ServiceLoader(['bar']);
        $loader->load(['type' => 'foo']);
    }

    public function testLoader()
    {
        $loader = new ServiceLoader(['foo.bar']);

        $definition = [
            'type' => 'foo.bar',
            'settings' => ['option2' => 23],
        ];

        $this->assertTrue($loader->support($definition));

        $this->assertInstanceOf('Sonata\BlockBundle\Model\BlockInterface', $loader->load($definition));
    }
}
