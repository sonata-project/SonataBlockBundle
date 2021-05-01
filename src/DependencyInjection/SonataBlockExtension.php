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

use Sonata\BlockBundle\Profiler\DataCollector\BlockDataCollector;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SonataBlockExtension extends Extension
{
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $defaultTemplates = [];
        if (isset($bundles['SonataPageBundle'])) {
            $defaultTemplates['SonataPageBundle default template'] = '@SonataPage/Block/block_container.html.twig';
        } else {
            $defaultTemplates['SonataBlockBundle default template'] = '@SonataBlock/Block/block_container.html.twig';
        }

        if (isset($bundles['SonataSeoBundle'])) {
            $defaultTemplates['SonataSeoBundle (to contain social buttons)'] = '@SonataSeo/Block/block_social_container.html.twig';
        }

        return new Configuration($defaultTemplates);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        $processor = new Processor();
        $configuration = $this->getConfiguration($configs, $container);
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('block.xml');

        if (isset($bundles['KnpMenuBundle'])) {
            $loader->load('knp_block.xml');
        }

        $loader->load('form.xml');
        $loader->load('core.xml');
        $loader->load('exception.xml');
        $loader->load('commands.xml');

        $this->configureBlockContainers($container, $config);
        $this->configureContext($container, $config);
        $this->configureLoaderChain($container, $config);
        $this->configureCache($container, $config);
        $this->configureForm($container, $config);
        $this->configureProfiler($container, $config);
        $this->configureException($container, $config);

        if (null === $config['templates']['block_base']) {
            if (isset($bundles['SonataPageBundle'])) {
                $config['templates']['block_base'] = '@SonataPage/Block/block_base.html.twig';
                $config['templates']['block_container'] = '@SonataPage/Block/block_container.html.twig';
            } else {
                $config['templates']['block_base'] = '@SonataBlock/Block/block_base.html.twig';
                $config['templates']['block_container'] = '@SonataBlock/Block/block_container.html.twig';
            }
        }

        $container->getDefinition('sonata.block.twig.global')->replaceArgument(0, $config['templates']);
    }

    public function configureBlockContainers(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('sonata.block.container.types', $config['container']['types']);

        $container->getDefinition('sonata.block.form.type.container_template')->replaceArgument(0, $config['container']['templates']);
    }

    public function configureContext(ContainerBuilder $container, array $config): void
    {
        $container->setParameter($this->getAlias().'.blocks', $config['blocks']);
        $container->setParameter($this->getAlias().'.blocks_by_class', $config['blocks_by_class']);

        $container->setAlias('sonata.block.context_manager', $config['context_manager']);
    }

    public function configureCache(ContainerBuilder $container, array $config): void
    {
        $container->setAlias('sonata.block.cache.handler', $config['http_cache']['handler']);

        if ($config['http_cache']['listener']) {
            $container->getDefinition($config['http_cache']['handler'])
                ->addTag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onKernelResponse']);
        }

        $cacheBlocks = [];
        foreach ($config['blocks'] as $service => $settings) {
            $cacheBlocks['by_type'][$service] = $settings['cache'];
        }
        foreach ($config['blocks_by_class'] as $class => $settings) {
            $cacheBlocks['by_class'][$class] = $settings['cache'];
        }

        $container->setParameter($this->getAlias().'.cache_blocks', $cacheBlocks);
    }

    public function configureLoaderChain(ContainerBuilder $container, array $config): void
    {
        $types = [];
        foreach ($config['blocks'] as $service => $settings) {
            $types[] = $service;
        }

        $container->setParameter('sonata_blocks.block_types', $types);
    }

    public function configureForm(ContainerBuilder $container, array $config): void
    {
        $defaults = $config['default_contexts'];

        $contexts = [];

        foreach ($config['blocks'] as $service => $settings) {
            if (0 === \count($settings['contexts'])) {
                $settings['contexts'] = $defaults;
            }

            foreach ($settings['contexts'] as $context) {
                if (!isset($contexts[$context])) {
                    $contexts[$context] = [];
                }

                $contexts[$context][] = $service;
            }
        }

        $container->setParameter('sonata_blocks.default_contexts', $defaults);
    }

    /**
     * Configures the block profiler.
     *
     * @param ContainerBuilder $container Container
     * @param array            $config    Configuration
     */
    public function configureProfiler(ContainerBuilder $container, array $config): void
    {
        $container->setAlias('sonata.block.renderer', 'sonata.block.renderer.default');

        if (!$config['profiler']['enabled']) {
            return;
        }

        // add the block data collector
        $definition = new Definition(BlockDataCollector::class);
        $definition->setPublic(false);
        $definition->addTag('data_collector', ['id' => 'block', 'template' => $config['profiler']['template']]);
        $definition->addArgument(new Reference('sonata.block.templating.helper'));
        $definition->addArgument($config['container']['types']);

        $container->setDefinition('sonata.block.data_collector', $definition);
    }

    /**
     * Configure the exception parameters.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    An array of configuration
     */
    public function configureException(ContainerBuilder $container, array $config): void
    {
        // retrieve available filters
        $filters = [];
        foreach ($config['exception']['filters'] as $name => $filter) {
            $filters[$name] = $filter;
        }

        // retrieve available renderers
        $renderers = [];
        foreach ($config['exception']['renderers'] as $name => $renderer) {
            $renderers[$name] = $renderer;
        }

        // retrieve block customization
        $blockFilters = [];
        $blockRenderers = [];
        foreach ($config['blocks'] as $service => $settings) {
            if (isset($settings['exception']['filter'])) {
                $blockFilters[$service] = $settings['exception']['filter'];
            }
            if (isset($settings['exception']['renderer'])) {
                $blockRenderers[$service] = $settings['exception']['renderer'];
            }
        }

        $definition = $container->getDefinition('sonata.block.exception.strategy.manager');
        $definition->replaceArgument(1, $filters);
        $definition->replaceArgument(2, $renderers);
        $definition->replaceArgument(3, $blockFilters);
        $definition->replaceArgument(4, $blockRenderers);

        // retrieve default values
        $defaultFilter = $config['exception']['default']['filter'];
        $defaultRenderer = $config['exception']['default']['renderer'];
        $definition->addMethodCall('setDefaultFilter', [$defaultFilter]);
        $definition->addMethodCall('setDefaultRenderer', [$defaultRenderer]);
    }

    public function getNamespace()
    {
        return 'http://sonata-project.com/schema/dic/block';
    }
}
