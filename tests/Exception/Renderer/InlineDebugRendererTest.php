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

/**
 * Test the inline debug exception renderer.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class InlineDebugRendererTest extends TestCase
{
    /**
     * test the renderer without debug mode.
     */
    public function testRenderWithoutDebug(): void
    {
        // GIVEN
        $template = 'test-template';
        $debug = false;
        $exception = $this->createMock('\Exception');
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $templating = $this->createMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');

        $renderer = new InlineDebugRenderer($templating, $template, $debug);

        // WHEN
        $response = $renderer->render($exception, $block);

        // THEN
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response, 'Should return a Response');
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
        $exception = $this->createMock('\Exception');

        // mock a block instance that provoked the exception
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        // mock the templating render() to return an html result
        $templating = $this->createMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo($template),
                $this->logicalAnd(
                    $this->arrayHasKey('exception'),
                    $this->callback(function ($subject) use ($block) {
                        $expected = [
                            'status_code' => 500,
                            'status_text' => 'Internal Server Error',
                            'logger' => false,
                            'currentContent' => false,
                            'block' => $block,
                            'forceStyle' => true,
                        ];

                        foreach ($expected as $key => $value) {
                            if (!array_key_exists($key, $subject) || $subject[$key] !== $value) {
                                return false;
                            }
                        }

                        return true;
                    })
                )
            )
            ->will($this->returnValue('html'));

        // create renderer to test
        $renderer = new InlineDebugRenderer($templating, $template, $debug);

        // WHEN
        $response = $renderer->render($exception, $block);

        // THEN
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response, 'Should return a Response');
        $this->assertEquals('html', $response->getContent(), 'Should contain the templating html result');
    }
}
