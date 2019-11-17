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
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * Test the inline exception renderer.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class InlineRendererTest extends TestCase
{
    /**
     * test the render() method.
     */
    public function testRender(): void
    {
        // GIVEN
        $template = 'test-template';

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
                $this->equalTo([
                    'exception' => $exception,
                    'block' => $block, ])
            )
            ->willReturn('html');

        // create renderer to test
        $renderer = new InlineRenderer($twig, $template);

        // WHEN
        $response = $renderer->render($exception, $block);

        // THEN
        $this->assertInstanceOf(Response::class, $response, 'Should return a Response');
        $this->assertSame('html', $response->getContent(), 'Should contain the templating html result');
    }
}
