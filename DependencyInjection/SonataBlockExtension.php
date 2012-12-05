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
        $this->configureProfiler($container, $config);

        $bundles = $container->getParameter('kernel.bundles');
        if ($config['templates']['block_base'] === null) {
            if (isset($bundles['SonataPageBundle'])) {
                $config['templates']['block_base'] = 'SonataPageBundle:Block:block_base.html.twig';
            } else {
                $config['templates']['block_base'] = 'SonataBlockBundle:Block:block_base.html.twig';
            }
        }

        $container->getDefinition('sonata.block.twig.global')
            ->replaceArgument(1, $config['templates']);
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

    /**
     * Configures the block profiler
     *
     * @param ContainerBuilder $container Container
     * @param array            $config    Configuration
     */
    public function configureProfiler(ContainerBuilder $container, array $config)
    {
        if (!$config['profiler']['enabled']) {
            return;
        }

        // replace renderer with a traceable renderer
        $renderer = $container->getDefinition('sonata.block.renderer');
        $renderer->setClass($config['profiler']['renderer_class']);

        // add the block data collector
        $definition = new Definition('Sonata\BlockBundle\Profiler\DataCollector\BlockDataCollector');
        $definition->addTag('data_collector', array('id' => 'block', 'template' => $config['profiler']['template']));
        $definition->addArgument(new Reference('sonata.block.renderer'));
        $container->addDefinitions(array($definition));
    }
}