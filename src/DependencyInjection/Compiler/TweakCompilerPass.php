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
 * @final since sonata-project/block-bundle 3.0
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TweakCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $manager = $container->getDefinition('sonata.block.manager');
        $registry = $container->getDefinition('sonata.block.menu.registry');

        $parameters = $container->getParameter('sonata_block.blocks');

        $blockTypes = $container->getParameter('sonata_blocks.block_types');

        foreach ($container->findTaggedServiceIds('sonata.block') as $serviceId => $tags) {
            // Skip manual defined blocks
            if (!isset($blockTypes[$serviceId])) {
                $contexts = $this->getContextFromTags($tags);
                $blockTypes[$serviceId] = [
                    'context' => $contexts,
                ];
            }

            $manager->addMethodCall('add', [$serviceId, $serviceId, isset($parameters[$serviceId]) ? $parameters[$serviceId]['contexts'] : []]);
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

    /**
     * @param string[][]
     *
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
