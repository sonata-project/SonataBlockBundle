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
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * Test the inline debug exception renderer.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class InlineDebugRendererTest extends TestCase
{
    /**
     * test the renderer without debug mode.
     */
    public function testRenderWithoutDebug(): void
    {
        // GIVEN
        $template = 'test-template';
        $debug = false;
        $exception = $this->createMock(\Exception::class);
        $block = $this->createMock(BlockInterface::class);
        $twig = $this->createMock(Environment::class);

        $renderer = new InlineDebugRenderer($twig, $template, $debug);

        // WHEN
        $response = $renderer->render($exception, $block);

        // THEN
        $this->assertInstanceOf(Response::class, $response, 'Should return a Response');
        $this->assertEmpty($response->getContent(), 'Should have no content');
    }

    /**
     * test the render() method with debug enabled.
     */
    public function testRenderWithDebugEnabled(): void
    {
        // GIVEN
        $template = 'test-template';
        $debug = true;

        // mock an exception to render
        $exception = $this->createMock(\Exception::class);

        // mock a block instance that provoked the exception
        $block = $this->createMock(BlockInterface::class);

        // mock the twig render() to return an html result
        $twig = $this->createMock(Environment::class);
        $twig->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo($template),
                $this->logicalAnd(
                    $this->arrayHasKey('exception'),
                    $this->callback(static function ($subject) use ($block) {
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

        // create renderer to test
        $renderer = new InlineDebugRenderer($twig, $template, $debug);

        // WHEN
        $response = $renderer->render($exception, $block);

        // THEN
        $this->assertInstanceOf(Response::class, $response, 'Should return a Response');
        $this->assertSame('html', $response->getContent(), 'Should contain the templating html result');
    }
}
