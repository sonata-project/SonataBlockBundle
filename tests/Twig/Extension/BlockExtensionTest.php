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
    private BlockHelper $blockHelper;

    private BlockExtension $blockExtension;

    private Environment $env;

    protected function setUp(): void
    {
        $this->blockHelper = $this->createMock(BlockHelper::class);

        $this->blockExtension = new BlockExtension();

        $this->env = new Environment($this->createMock(LoaderInterface::class));
        $this->env->addExtension($this->blockExtension);
        $this->env->addRuntimeLoader(new FactoryRuntimeLoader([
            BlockHelper::class => fn (): BlockHelper => $this->blockHelper,
        ]));
    }

    /**
     * @return iterable<array-key, array{string, array<mixed>, string}>
     */
    public static function provideFunctionCases(): iterable
    {
        yield ['sonata_block_exists', [
            'block_name',    // arguments
        ], 'exists'];
        yield ['sonata_block_render', [
            'foobar', ['bar' => 'foo'],    // arguments
        ], 'render'];
        yield ['sonata_block_include_javascripts', [
            'screen',                         // arguments
        ], 'includeJavascripts'];
        yield ['sonata_block_include_stylesheets', [
            'foo',                            // arguments
        ], 'includeStylesheets'];
        yield ['sonata_block_render_event', [
            'event.name', [],            // arguments
        ], 'renderEvent'];
    }

    /**
     * @param mixed[] $args
     *
     * @dataProvider provideFunctionCases
     */
    public function testFunction(string $name, array $args, string $expectedMethod): void
    {
        $this->blockHelper->expects(static::once())
            ->method($expectedMethod)
            ->with(...$args);

        /** @psalm-suppress InternalMethod */
        $func = $this->env->getFunction($name);

        static::assertInstanceOf(TwigFunction::class, $func);

        $callable = [$this->env->getRuntime(BlockHelper::class), $expectedMethod];
        static::assertIsCallable($callable);
        \call_user_func_array($callable, $args);
    }
}
