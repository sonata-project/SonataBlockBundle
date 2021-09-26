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

namespace Sonata\BlockBundle\Tests\Twig\Extension;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\BlockBundle\Twig\Extension\BlockExtension;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFunction;

final class BlockExtensionTest extends TestCase
{
    /**
     * @var MockObject&BlockHelper
     */
    private $blockHelper;

    /**
     * @var BlockExtension
     */
    private $blockExtension;

    /**
     * @var Environment
     */
    private $env;

    protected function setUp(): void
    {
        $this->blockHelper = $this->createMock(BlockHelper::class);

        $this->blockExtension = new BlockExtension();

        $this->env = new Environment($this->createMock(LoaderInterface::class));
        $this->env->addExtension($this->blockExtension);
        $this->env->addRuntimeLoader(new FactoryRuntimeLoader([
            BlockHelper::class => function (): BlockHelper {
                return $this->blockHelper;
            },
        ]));
    }

    /**
     * @return iterable<array-key, array{string, array<mixed>, string}>
     */
    public function provideFunction(): iterable
    {
        return [
            ['sonata_block_exists', [
                'block_name',    // arguments
            ], 'exists'],
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
     * @param mixed[] $args
     *
     * @dataProvider provideFunction
     */
    public function testFunction(string $name, array $args, string $expectedMethod): void
    {
        $this->blockHelper->expects(static::once())
            ->method($expectedMethod)
            ->with(...$args);

        /** @psalm-suppress InternalMethod */
        $func = $this->env->getFunction($name);

        static::assertInstanceOf(TwigFunction::class, $func);
        \call_user_func_array([$this->env->getRuntime(BlockHelper::class), $expectedMethod], $args);
    }
}
