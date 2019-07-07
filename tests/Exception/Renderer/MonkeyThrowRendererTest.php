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
use Sonata\BlockBundle\Exception\Renderer\MonkeyThrowRenderer;

/**
 * Test the monkey throw exception renderer.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class MonkeyThrowRendererTest extends TestCase
{
    /**
     * test the render() method with a standard Exception.
     */
    public function testRenderWithStandardException()
    {
        $this->expectException(\Exception::class);

        // GIVEN
        $exception = new \Exception();
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $renderer = new MonkeyThrowRenderer();

        // WHEN
        $renderer->render($exception, $block);

        // THEN
        // exception expected
    }

    /**
     * test the render() method with another exception to ensure it correctly throws the provided exception.
     */
    public function testRenderWithRuntimeException()
    {
        $this->expectException(\RuntimeException::class);

        // GIVEN
        $exception = new \RuntimeException();
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $renderer = new MonkeyThrowRenderer();

        // WHEN
        $renderer->render($exception, $block);

        // THEN
        // exception expected
    }
}
