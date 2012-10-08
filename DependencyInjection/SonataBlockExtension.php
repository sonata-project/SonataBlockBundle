<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataBlockExtension extends Extension
{
    /**
     * Loads the url shortener configuration.
     *
     * @param array            $configs    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('block.xml');
        $loader->load('form.xml');
        $loader->load('core.xml');

        $this->configureLoaderChain($container, $config);
        $this->configureCache($container, $config);
        $this->configureForm($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     *
     * @return void
     */
    public function configureCache(ContainerBuilder $container, array $config)
    {
        $cacheBlocks = array();
        foreach ($config['blocks'] as $service => $settings) {
            $cacheBlocks[$service] = $settings['cache'];
        }

        $container->getDefinition('sonata.block.twig.extension')->replaceArgument(1, $cacheBlocks);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     *
     * @return void
     */
    public function configureLoaderChain(ContainerBuilder $container, array $config)
    {
        $configs = array();
        foreach ($config['blocks'] as $service => $settings) {
            $configs[$service] = $settings['settings'];
        }

        $container->getDefinition('sonata.block.loader.service')->replaceArgument(0, $configs);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     *
     * @return void
     */
    public function configureForm(ContainerBuilder $container, array $config)
    {
        $defaults = $config['default_contexts'];

        $contexts = array();

        foreach ($config['blocks'] as $service => $settings) {
            if (count($settings['contexts']) == 0) {
                $settings['contexts'] = $defaults;
            }

            foreach ($settings['contexts'] as $context) {
                if (!isset($contexts[$context])) {
                    $contexts[$context] = array();
                }

                $contexts[$context][] = $service;
            }
        }

        $container->getDefinition('sonata.block.form.type.block')
            ->replaceArgument(1, $contexts);
    }
}