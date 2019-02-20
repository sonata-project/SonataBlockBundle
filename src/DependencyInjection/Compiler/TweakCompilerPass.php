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

use Sonata\BlockBundle\Naming\ConvertFromFqcn;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Link the block service to the Page Manager.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TweakCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $manager = $container->getDefinition('sonata.block.manager');
        $registry = $container->getDefinition('sonata.block.menu.registry');

        $parameters = $container->getParameter('sonata_block.blocks');

        $blockTypes = $container->getParameter('sonata_blocks.block_types');

        foreach ($container->findTaggedServiceIds('sonata.block') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $definition->setPublic(true);

            if (!$definition->isAutowired()) {
                $this->replaceBlockName($container, $definition, $id);
            }

            $blockId = $id;

            // Only convert class service names
            if (false !== strpos($blockId, '\\')) {
                $convert = (new ConvertFromFqcn());
                $blockId = $convert($blockId);
            }

            // Skip manual defined blocks
            if (!isset($blockTypes[$blockId])) {
                $contexts = $this->getContextFromTags($tags);
                $blockTypes[$blockId] = [
                    'context' => $contexts,
                ];
            }

            $manager->addMethodCall('add', [$id, $id, isset($parameters[$id]) ? $parameters[$id]['contexts'] : []]);
        }

        foreach ($container->findTaggedServiceIds('knp_menu.menu') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (empty($attributes['alias'])) {
                    throw new \InvalidArgumentException(sprintf('The alias is not defined in the "knp_menu.menu" tag for the service "%s"', $id));
                }
                $registry->addMethodCall('add', [$attributes['alias']]);
            }
        }

        $services = [];
        foreach ($container->findTaggedServiceIds('sonata.block.loader') as $id => $tags) {
            $services[] = new Reference($id);
        }

        $container->getDefinition('sonata.block.loader.service')->replaceArgument(0, $blockTypes);
        $container->getDefinition('sonata.block.loader.chain')->replaceArgument(0, $services);

        $this->applyContext($container);
    }

    /**
     * Apply configurations to the context manager.
     *
     * @param ContainerBuilder $container
     */
    public function applyContext(ContainerBuilder $container)
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
     * Replaces the empty service name with the service id.
     */
    private function replaceBlockName(ContainerBuilder $container, Definition $definition, $id)
    {
        $arguments = $definition->getArguments();

        // Replace empty block id with service id
        if (empty($arguments) || 0 === \strlen($arguments[0])) {
            // NEXT_MAJOR: Remove the if block when Symfony 2.8 support will be dropped.
            if (method_exists($definition, 'setArgument')) {
                $definition->setArgument(0, $id);

                return;
            }

            $definition->replaceArgument(0, $id);

            return;
        }

        if ($id !== $arguments[0] && 0 !== strpos(
            (string) $container->getParameterBag()->resolveValue($definition->getClass()),
            'Sonata\\BlockBundle\\Block\\Service\\'
        )) {
            // NEXT_MAJOR: Remove deprecation notice
            @trigger_error(
                sprintf('Using service id %s different from block id %s is deprecated since 3.3 and will be removed in 4.0.', $id, $arguments[0]),
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * @param string[][]
     *
     * @return string[]
     */
    private function getContextFromTags(array $tags)
    {
        return array_filter(array_map(function (array $attribute) {
            if (\array_key_exists('context', $attribute) && \is_string($attribute['context'])) {
                return $attribute['context'];
            }

            return null;
        }, $tags));
    }
}
