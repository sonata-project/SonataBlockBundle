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

namespace Sonata\BlockBundle\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;

class BlockExtensionTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|BlockHelper
     */
    protected $blockHelper;

    /**
     * @var BlockExtension
     */
    protected $blockExtension;

    /**
     * @var \Twig_Environment
     */
    protected $env;

    public function setUp(): void
    {
        $this->blockHelper = $this->getMockBuilder(
            'Sonata\BlockBundle\Templating\Helper\BlockHelper'
        )->disableOriginalConstructor()->getMock();

        $loader = $this->createMock('Twig_LoaderInterface');

        $this->blockExtension = new BlockExtension($this->blockHelper);

        $this->env = new \Twig_Environment($loader);
        $this->env->addExtension($this->blockExtension);
    }

    public function provideFunction()
    {
        return [
            ['sonata_block_render', [
                'foobar', ['bar' => 'foo'],    // arguments
            ], 'render'],
            ['sonata_block_include_javascripts', [
                'screen',                         // arguments
            ], 'includeJavascripts'],
            ['sonata_block_include_stylesheets', [
                'foo',                            // arguments
            ], 'includeStylesheets'],
            ['sonata_block_render_event', [
                'event.name', [],            // arguments
            ], 'renderEvent'],
        ];
    }

    /**
     * @dataProvider provideFunction
     */
    public function testFunction($name, $args, $expectedMethod): void
    {
        $this->blockHelper->expects($this->once())
            ->method($expectedMethod);

        $func = $this->env->getFunction($name);
        $this->assertInstanceOf('Twig_SimpleFunction', $func);
        \call_user_func_array($func->getCallable(), $args);
    }
}
