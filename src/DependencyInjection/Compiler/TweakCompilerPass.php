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

namespace Sonata\BlockBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Link the block service to the Page Manager.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class TweakCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $manager = $container->getDefinition('sonata.block.manager');
        $registry = $container->getDefinition('sonata.block.menu.registry');

        $blocks = $container->getParameter('sonata_block.blocks');
        $blockTypes = $container->getParameter('sonata_blocks.block_types');
        $cacheBlocks = $container->getParameter('sonata_block.cache_blocks');
        $defaultContexs = $container->getParameter('sonata_blocks.default_contexts');

        foreach ($container->findTaggedServiceIds('sonata.block') as $id => $tags) {
            $container->getDefinition($id)
                ->setPublic(true);

            $settings = $this->createBlockSettings($tags, $defaultContexs);

            // Register blocks dynamicaly
            if (!\array_key_exists($id, $blocks)) {
                $blocks[$id] = $settings;
            }
            if (!\in_array($id, $blockTypes, true)) {
                $blockTypes[] = $id;
            }
            if (isset($cacheBlocks['by_type']) && !\array_key_exists($id, $cacheBlocks['by_type'])) {
                $cacheBlocks['by_type'][$id] = $settings['cache'];
            }

            $manager->addMethodCall('add', [$id, $id, $settings['contexts']]);
        }

        foreach ($container->findTaggedServiceIds('knp_menu.menu') as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                if (empty($attributes['alias'])) {
                    throw new \InvalidArgumentException(sprintf('The alias is not defined in the "knp_menu.menu" tag for the service "%s"', $serviceId));
                }
                $registry->addMethodCall('add', [$attributes['alias']]);
            }
        }

        $services = [];
        foreach ($container->findTaggedServiceIds('sonata.block.loader') as $serviceId => $tags) {
            $services[] = new Reference($serviceId);
        }

        $container->setParameter('sonata_block.blocks', $blocks);
        $container->setParameter('sonata_blocks.block_types', $blockTypes);
        $container->setParameter('sonata_block.cache_blocks', $cacheBlocks);

        $container->getDefinition('sonata.block.loader.service')->replaceArgument(0, $blockTypes);
        $container->getDefinition('sonata.block.loader.chain')->replaceArgument(0, $services);

        $this->applyContext($container);
    }

    /**
     * Apply configurations to the context manager.
     */
    public function applyContext(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('sonata.block.context_manager');

        foreach ($container->getParameter('sonata_block.blocks') as $service => $settings) {
            if (\count($settings['settings']) > 0) {
                $definition->addMethodCall('addSettingsByType', [$service, $settings['settings'], true]);
            }
        }
        foreach ($container->getParameter('sonata_block.blocks_by_class') as $class => $settings) {
            if (\count($settings['settings']) > 0) {
                $definition->addMethodCall('addSettingsByClass', [$class, $settings['settings'], true]);
            }
        }
    }

    private function createBlockSettings(array $tags = [], array $defaultContexts = []): array
    {
        $contexts = $this->getContextFromTags($tags);

        if (0 === \count($contexts)) {
            $contexts = $defaultContexts;
        }

        return [
            'contexts' => $contexts,
            'templates' => [],
            'cache' => 'sonata.cache.noop',
            'settings' => [],
        ];
    }

    /**
     * @return string[]
     */
    private function getContextFromTags(array $tags)
    {
        return array_filter(array_map(static function (array $attribute) {
            if (\array_key_exists('context', $attribute) && \is_string($attribute['context'])) {
                return $attribute['context'];
            }

            return null;
        }, $tags));
    }
}
