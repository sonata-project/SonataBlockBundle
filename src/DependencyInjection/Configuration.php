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

namespace Sonata\BlockBundle\DependencyInjection;

use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * NEXT_MAJOR: remove this member.
     */
    private bool $httpCacheDisabled = false;

    /**
     * @param array<string, string> $defaultContainerTemplates
     */
    public function __construct(private array $defaultContainerTemplates)
    {
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sonata_block');

        $node = $treeBuilder->getRootNode();

        $node
            ->fixXmlConfig('default_context')
            ->fixXmlConfig('template')
            ->fixXmlConfig('block')
            ->fixXmlConfig('block_by_class')
            ->validate()
                ->always(function (&$value) {
                    foreach ($value['blocks'] as &$block) {
                        if (0 === \count($block['contexts'])) {
                            $block['contexts'] = $value['default_contexts'];
                        }
                    }

                    // NEXT_MAJOR: remove this block
                    if (true !== $this->httpCacheDisabled) {
                        @trigger_error(
                            'Not setting the "sonata_block.http_cache" config option to false is deprecated since sonata-project/block-bundle 4.11 and will fail in 5.0.',
                            \E_USER_DEPRECATED
                        );
                    } else {
                        $value['http_cache'] = false;
                    }

                    return $value;
                })
            ->end()
            ->children()
                ->arrayNode('profiler')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultValue('%kernel.debug%')->end()
                        ->scalarNode('template')->defaultValue('@SonataBlock/Profiler/block.html.twig')->end()
                    ->end()
                ->end()

                ->arrayNode('default_contexts')
                    ->prototype('scalar')->end()
                ->end()

                ->scalarNode('context_manager')->defaultValue('sonata.block.context_manager.default')->end()
                // NEXT_MAJOR: deprecate option and only allow setting it to false
                ->arrayNode('http_cache')
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->always(function ($v) {
                            if (false === $v) {
                                $this->httpCacheDisabled = true;

                                return [];
                            }

                            return $v;
                        })
                    ->end()
                    ->children()
                        // NEXT_MAJOR: remove option
                        ->scalarNode('handler')->defaultValue('sonata.block.cache.handler.default')->end()
                        // NEXT_MAJOR: remove option
                        ->booleanNode('listener')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('block_base')->defaultNull()->end()
                        ->scalarNode('block_container')->defaultNull()->end()
                    ->end()
                ->end()

                ->arrayNode('container')
                    ->info('block container configuration')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('type', 'types')
                    ->fixXmlConfig('template', 'templates')
                    ->children()
                        ->arrayNode('types')
                            ->info('container service ids')
                            ->isRequired()
                            // add default value to well know users of BlockBundle
                            ->defaultValue(['sonata.block.service.container', 'sonata.page.block.container', 'sonata.dashboard.block.container', 'cmf.block.container', 'cmf.block.slideshow'])
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('templates')
                            ->info('container templates')
                            ->isRequired()
                            ->defaultValue($this->defaultContainerTemplates)
                            ->prototype('scalar')->end()
                        ->end()

                    ->end()
                ->end()

                ->arrayNode('blocks')
                    ->info('configuration per block service')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->fixXmlConfig('context')
                        ->fixXmlConfig('template')
                        ->fixXmlConfig('setting')
                        ->children()
                            ->arrayNode('contexts')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('templates')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->cannotBeEmpty()->end()
                                        ->scalarNode('template')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                            // NEXT_MAJOR: remove cache option
                            ->scalarNode('cache')
                                ->defaultValue('sonata.cache.noop')
                                ->setDeprecated(
                                    ...$this->getDeprecationMessage(
                                        'The "cache" option for configuring blocks is deprecated since sonata-project/block-bundle 4.11 and will be removed in 5.0.',
                                        '4.11'
                                    )
                                )
                            ->end()
                            ->arrayNode('settings')
                                ->info('default settings')
                                ->useAttributeAsKey('id')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('exception')
                                ->children()
                                    ->scalarNode('filter')->defaultNull()->end()
                                    ->scalarNode('renderer')->defaultNull()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('blocks_by_class')
                    ->info('configuration per block class')
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        ->fixXmlConfig('setting')
                        ->children()
                            ->scalarNode('cache')
                                ->defaultValue('sonata.cache.noop')
                                ->setDeprecated(
                                    ...$this->getDeprecationMessage(
                                        'The "cache" option for configuring blocks_by_class is deprecated since sonata-project/block-bundle 4.11 and will be removed in 5.0.',
                                        '4.11'
                                    )
                                )
                            ->end()
                            ->arrayNode('settings')
                                ->info('default settings')
                                ->useAttributeAsKey('id')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('exception')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('filter')
                    ->fixXmlConfig('renderer')
                    ->children()
                        ->arrayNode('default')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('filter')->defaultValue('debug_only')->end()
                                ->scalarNode('renderer')->defaultValue('throw')->end()
                            ->end()
                        ->end()

                        ->arrayNode('filters')
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                            ->defaultValue([
                                'debug_only' => 'sonata.block.exception.filter.debug_only',
                                'ignore_block_exception' => 'sonata.block.exception.filter.ignore_block_exception',
                                'keep_all' => 'sonata.block.exception.filter.keep_all',
                                'keep_none' => 'sonata.block.exception.filter.keep_none',
                            ])
                        ->end()
                        ->arrayNode('renderers')
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                            ->defaultValue([
                                'inline' => 'sonata.block.exception.renderer.inline',
                                'inline_debug' => 'sonata.block.exception.renderer.inline_debug',
                                'throw' => 'sonata.block.exception.renderer.throw',
                            ])
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new self([]);
    }

    /**
     * @return string[]
     */
    private function getDeprecationMessage(string $message, string $version): array
    {
        // @phpstan-ignore-next-line
        if (method_exists(BaseNode::class, 'getDeprecation')) {
            return [
                'sonata-project/block-bundle',
                $version,
                $message,
            ];
        }

        return [$message];
    }
}
