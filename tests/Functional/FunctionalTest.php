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

namespace Sonata\BlockBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FunctionalTest extends WebTestCase
{
    public function testRenderBlock(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertSame(200, $client->getResponse()->getStatusCode());
    }
}
