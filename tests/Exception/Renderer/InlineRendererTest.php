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
use Sonata\BlockBundle\Exception\Renderer\InlineRenderer;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class InlineRendererTest extends TestCase
{
    public function testRender(): void
    {
        $template = 'test-template';

        $exception = $this->createMock(\Exception::class);
        $block = $this->createMock(BlockInterface::class);

        $twig = $this->createMock(Environment::class);
        $twig->expects(static::once())
            ->method('render')
            ->with(
                static::equalTo($template),
                static::equalTo([
                    'exception' => $exception,
                    'block' => $block, ])
            )
            ->willReturn('html');

        $renderer = new InlineRenderer($twig, $template);

        $response = $renderer->render($exception, $block);

        static::assertInstanceOf(Response::class, $response, 'Should return a Response');
        static::assertSame('html', $response->getContent(), 'Should contain the templating html result');
    }
}
