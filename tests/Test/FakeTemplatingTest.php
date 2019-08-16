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

namespace Sonata\BlockBundle\Tests\Test;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Test\FakeTemplating;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @deprecated since sonata-project/block-bundle 3.17, will be removed in version 4.0.
 */
final class FakeTemplatingTest extends TestCase
{
    public function testRender()
    {
        $templating = new FakeTemplating();
        $templating->render('template.html.twig', [
            'foo' => 'bar',
        ]);

        $this->assertSame('template.html.twig', $templating->name);
        $this->assertSame([
            'foo' => 'bar',
        ], $templating->parameters);
    }

    public function testRenderResponse()
    {
        $response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();

        $templating = new FakeTemplating();
        $templating->renderResponse('template.html.twig', [
            'foo' => 'bar',
        ], $response);

        $this->assertSame('template.html.twig', $templating->view);
        $this->assertSame([
            'foo' => 'bar',
        ], $templating->parameters);
        $this->assertSame($response, $templating->response);
    }

    public function testSupports()
    {
        $templating = new FakeTemplating();
        $this->assertTrue($templating->supports('foo'));
    }

    /**
     * {@inheritdoc}
     */
    public function testExists()
    {
        $templating = new FakeTemplating();
        $this->assertTrue($templating->exists('foo'));
    }
}
