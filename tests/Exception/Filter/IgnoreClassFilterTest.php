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
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Test the ignore final class exception filter.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class IgnoreClassFilterTest extends TestCase
{
    /**
     * test the filter with a inherited exception.
     */
    public function testWithInheritedException(): void
    {
        // GIVEN
        $exception = $this->createMock(NotFoundHttpException::class);
        $block = $this->createMock(BlockInterface::class);
        $filter = new IgnoreClassFilter(\RuntimeException::class);

        // WHEN
        $result = $filter->handle($exception, $block);

        // THEN
        static::assertFalse($result, 'Should NOT handle it since NotFoundHttpException inherits RuntimeException');
    }

    /**
     * test the the filter with a non-inherited exception.
     */
    public function testWithNonInheritedException(): void
    {
        // GIVEN
        $exception = $this->createMock(\Exception::class);
        $block = $this->createMock(BlockInterface::class);
        $filter = new IgnoreClassFilter(\RuntimeException::class);

        // WHEN
        $result = $filter->handle($exception, $block);

        // THEN
        static::assertTrue($result, 'Should handle it since an \Exception does not inherit RuntimeException');
    }
}
