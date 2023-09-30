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
use Sonata\BlockBundle\Exception\Filter\KeepAllFilter;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class KeepAllFilterTest extends TestCase
{
    /**
     * @dataProvider provideFilterCases
     */
    public function testFilter(\Exception $exception): void
    {
        $block = $this->createMock(BlockInterface::class);
        $filter = new KeepAllFilter();

        $result = $filter->handle($exception, $block);

        static::assertTrue($result, 'Should handle any exception');
    }

    /**
     * @return iterable<array-key, array{\Exception}>
     */
    public static function provideFilterCases(): iterable
    {
        yield [new \Exception()];
        yield [new \RuntimeException()];
    }
}
