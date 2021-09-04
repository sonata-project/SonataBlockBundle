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

namespace Sonata\BlockBundle\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Cache\HttpCacheHandlerInterface;
use Sonata\BlockBundle\Cache\NoopHttpCacheHandler;

final class NoopHttpCacheHandlerTest extends TestCase
{
    public function testInterface(): void
    {
        static::assertInstanceOf(
            HttpCacheHandlerInterface::class,
            new NoopHttpCacheHandler()
        );
    }
}
