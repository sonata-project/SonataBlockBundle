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

namespace Sonata\BlockBundle\Tests\Block;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\Loader\ServiceLoader;
use Sonata\BlockBundle\Model\BlockInterface;

final class ServiceLoaderTest extends TestCase
{
    public function testBlockNotFoundException(): void
    {
        $this->expectException(\RuntimeException::class);

        $loader = new ServiceLoader(['bar']);
        $loader->load(['type' => 'foo']);
    }

    public function testLoader(): void
    {
        $loader = new ServiceLoader(['foo.bar']);

        $definition = [
            'type' => 'foo.bar',
            'settings' => ['option2' => 23],
        ];

        static::assertTrue($loader->support($definition));

        static::assertInstanceOf(BlockInterface::class, $loader->load($definition));
    }
}
