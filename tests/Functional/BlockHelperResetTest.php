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

use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Service\ResetInterface;

final class BlockHelperResetTest extends KernelTestCase
{
    public function testTracesAreClearedWhenServicesAreReset(): void
    {
        $container = static::getContainer();

        $helper = $container->get('sonata.block.templating.helper');
        static::assertInstanceOf(BlockHelper::class, $helper);

        $helper->render(['type' => 'sonata.block.service.text'], ['content' => 'foo']);
        $helper->renderEvent('test.event');

        // a worker runtime (FrankenPHP, RoadRunner, ...) resets services between two requests
        $resetter = $container->get('services_resetter');
        static::assertInstanceOf(ResetInterface::class, $resetter);
        $resetter->reset();

        static::assertSame(['_events' => []], $helper->getTraces());
    }
}
