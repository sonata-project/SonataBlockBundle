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
use Sonata\BlockBundle\Exception\Renderer\InlineDebugRenderer;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class InlineDebugRendererTest extends TestCase
{
    public function testRenderWithoutDebug(): void
    {
        $template = 'test-template';
        $debug = false;
        $exception = $this->createMock(\Exception::class);
        $block = $this->createMock(BlockInterface::class);
        $twig = $this->createMock(Environment::class);

        $renderer = new InlineDebugRenderer($twig, $template, $debug);

        $response = $renderer->render($exception, $block);

        static::assertInstanceOf(Response::class, $response, 'Should return a Response');
        static::assertEmpty($response->getContent(), 'Should have no content');
    }

    public function testRenderWithDebugEnabled(): void
    {
        $template = 'test-template';
        $debug = true;

        $exception = $this->createMock(\Exception::class);
        $block = $this->createMock(BlockInterface::class);

        $twig = $this->createMock(Environment::class);
        $twig->expects(static::once())
            ->method('render')
            ->with(
                static::equalTo($template),
                static::logicalAnd(
                    static::arrayHasKey('exception'),
                    static::callback(static function ($subject) use ($block): bool {
                        $expected = [
                            'status_code' => 500,
                            'status_text' => 'Internal Server Error',
                            'logger' => false,
                            'currentContent' => false,
                            'block' => $block,
                            'forceStyle' => true,
                        ];

                        foreach ($expected as $key => $value) {
                            if (!\array_key_exists($key, $subject) || $subject[$key] !== $value) {
                                return false;
                            }
                        }

                        return true;
                    })
                )
            )
            ->willReturn('html');

        $renderer = new InlineDebugRenderer($twig, $template, $debug);

        $response = $renderer->render($exception, $block);

        static::assertInstanceOf(Response::class, $response, 'Should return a Response');
        static::assertSame('html', $response->getContent(), 'Should contain the templating html result');
    }
}
