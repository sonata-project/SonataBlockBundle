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

/**
 * Test the debug only exception filter.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class DebugOnlyFilterTest extends TestCase
{
    /**
     * test the filter with debug enabled.
     */
    public function testWithDebugEnabled()
    {
        // GIVEN
        $exception = $this->createMock('\Exception');
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $filter = new DebugOnlyFilter(true);

        // WHEN
        $result = $filter->handle($exception, $block);

        // THEN
        $this->assertTrue($result, 'Should handle it since we have enabled debug');
    }

    /**
     * test the filter with debug disabled.
     */
    public function testWithDebugDisabled()
    {
        // GIVEN
        $exception = $this->createMock('\Exception');
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $filter = new DebugOnlyFilter(false);

        // WHEN
        $result = $filter->handle($exception, $block);

        // THEN
        $this->assertFalse($result, 'Should NOT handle it since we have disabled debug');
    }
}
