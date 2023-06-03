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

namespace Sonata\BlockBundle\Tests;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    /**
     * @param string[] $contexts
     *
     * @dataProvider providerContexts
     */
    public function testOptions(array $contexts): void
    {
        $defaultTemplates = [
            '@SonataPage/Block/block_container.html.twig' => 'SonataPageBundle template',
            '@SonataSeo/Block/block_social_container.html.twig' => 'SonataSeoBundle (to contain social buttons)',
        ];

        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration($defaultTemplates), [[
            'default_contexts' => $contexts,
            'blocks' => [
                'my.block.type' => [],
                'my.block_with_context.type' => ['context' => 'custom'],
            ],
        ]]);

        $expected = [
            'default_contexts' => $contexts,
            'blocks' => [
                'my.block.type' => [
                    'contexts' => $contexts,
                    'templates' => [],
                    'settings' => [],
                ],
                'my.block_with_context.type' => [
                    'contexts' => ['custom'],
                    'templates' => [],
                    'settings' => [],
                ],
            ],
            'profiler' => [
                'enabled' => '%kernel.debug%',
                'template' => '@SonataBlock/Profiler/block.html.twig',
            ],
            'context_manager' => 'sonata.block.context_manager.default',
            'http_cache' => false,
            'templates' => [
                'block_base' => null,
                'block_container' => null,
            ],
            'container' => [
                'types' => [
                    0 => 'sonata.block.service.container',
                    1 => 'sonata.page.block.container',
                    2 => 'sonata.dashboard.block.container',
                    3 => 'cmf.block.container',
                    4 => 'cmf.block.slideshow',
                ],
                'templates' => $defaultTemplates,
            ],
            'blocks_by_class' => [],
            'exception' => [
                'default' => [
                    'filter' => 'debug_only',
                    'renderer' => 'throw',
                ],
                'filters' => [
                    'debug_only' => 'sonata.block.exception.filter.debug_only',
                    'ignore_block_exception' => 'sonata.block.exception.filter.ignore_block_exception',
                    'keep_all' => 'sonata.block.exception.filter.keep_all',
                    'keep_none' => 'sonata.block.exception.filter.keep_none',
                ],
                'renderers' => [
                    'inline' => 'sonata.block.exception.renderer.inline',
                    'inline_debug' => 'sonata.block.exception.renderer.inline_debug',
                    'throw' => 'sonata.block.exception.renderer.throw',
                ],
            ],
        ];

        static::assertSame($expected, $config);
    }

    /**
     * @return iterable<array-key, array{array<string>}>
     */
    public static function providerContexts(): iterable
    {
        return [
            [[]],
            [['cms']],
            [['cms', 'sonata_page_bundle']],
        ];
    }
}
