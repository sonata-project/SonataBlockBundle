<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sonata_block')->children();

        $node
            ->arrayNode('default_contexts')
                ->isRequired()
                ->prototype('scalar')->end()
            ->end()

            ->arrayNode('templates')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('block_base')->defaultValue(null)->end()
                ->end()
            ->end()

            ->arrayNode('blocks')
                ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->arrayNode('contexts')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('cache')->defaultValue('sonata.cache.noop')->end()
                        ->arrayNode('settings')
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
