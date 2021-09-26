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

namespace Sonata\BlockBundle\Tests\Exception\Renderer;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Exception\Filter\DebugOnlyFilter;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class DebugOnlyFilterTest extends TestCase
{
    public function testWithDebugEnabled(): void
    {
        $exception = $this->createMock(\Exception::class);
        $block = $this->createMock(BlockInterface::class);
        $filter = new DebugOnlyFilter(true);

        $result = $filter->handle($exception, $block);

        static::assertTrue($result, 'Should handle it since we have enabled debug');
    }

    public function testWithDebugDisabled(): void
    {
        $exception = $this->createMock(\Exception::class);
        $block = $this->createMock(BlockInterface::class);
        $filter = new DebugOnlyFilter(false);

        $result = $filter->handle($exception, $block);

        static::assertFalse($result, 'Should NOT handle it since we have disabled debug');
    }
}
