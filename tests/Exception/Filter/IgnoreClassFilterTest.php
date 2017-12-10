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
use Sonata\BlockBundle\Exception\Filter\IgnoreClassFilter;

/**
 * Test the ignore class exception filter.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class IgnoreClassFilterTest extends TestCase
{
    /**
     * test the filter with a inherited exception.
     */
    public function testWithInheritedException(): void
    {
        // GIVEN
        $exception = $this->createMock('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $filter = new IgnoreClassFilter('\RuntimeException');

        // WHEN
        $result = $filter->handle($exception, $block);

        // THEN
        $this->assertFalse($result, 'Should NOT handle it since NotFoundHttpException inherits RuntimeException');
    }

    /**
     * test the the filter with a non-inherited exception.
     */
    public function testWithNonInheritedException(): void
    {
        // GIVEN
        $exception = $this->createMock('\Exception');
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $filter = new IgnoreClassFilter('\RuntimeException');

        // WHEN
        $result = $filter->handle($exception, $block);

        // THEN
        $this->assertTrue($result, 'Should handle it since an \Exception does not inherit RuntimeException');
    }
}
