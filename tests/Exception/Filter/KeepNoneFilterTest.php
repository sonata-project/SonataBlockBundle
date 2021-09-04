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
use Sonata\BlockBundle\Exception\Filter\KeepNoneFilter;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * Test the keep all exception filter.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class KeepNoneFilterTest extends TestCase
{
    /**
     * test the filter with an exception.
     *
     * @dataProvider getExceptions
     */
    public function testFilter(\Exception $exception): void
    {
        // GIVEN
        $block = $this->createMock(BlockInterface::class);
        $filter = new KeepNoneFilter();

        // WHEN
        $result = $filter->handle($exception, $block);

        // THEN
        static::assertFalse($result, 'Should handle no exceptions');
    }

    /**
     * Returns exceptions to test.
     *
     * @return array
     */
    public function getExceptions()
    {
        return [
            [$this->createMock(\Exception::class)],
            [$this->createMock(\RuntimeException::class)],
        ];
    }
}
