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
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class MonkeyThrowRendererTest extends TestCase
{
    public function testRenderWithStandardException(): void
    {
        $this->expectException(\Exception::class);

        $exception = new \Exception();
        $block = $this->createMock(BlockInterface::class);
        $renderer = new MonkeyThrowRenderer();

        $renderer->render($exception, $block);
    }

    public function testRenderWithRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $exception = new \RuntimeException();
        $block = $this->createMock(BlockInterface::class);
        $renderer = new MonkeyThrowRenderer();

        $renderer->render($exception, $block);
    }
}
