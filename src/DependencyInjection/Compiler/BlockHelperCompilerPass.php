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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 *
 * NEXT_MAJOR: remove this class
 */
final class BlockHelperCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var array{by_class: array<class-string, string>, by_type: array<string, string>} $cacheBlocks */
        $cacheBlocks = $container->getParameter('sonata_block.cache_blocks');

        $hasCacheBlocks = false;
        foreach ($cacheBlocks as $blocks) {
            foreach ($blocks as $cacheType) {
                $hasCacheBlocks = $hasCacheBlocks || 'sonata.cache.noop' !== $cacheType;
            }
        }

        if (!$hasCacheBlocks) {
            return;
        }

        @trigger_error(
            'Defining cache blocks other than \'sonata.cache.noop\' is deprecated since sonata-project/block-bundle 4.11 and will not be supported anymore in 5.0.',
            \E_USER_DEPRECATED
        );

        $blockHelperDefinition = $container->getDefinition('sonata.block.templating.helper');
        $blockHelperDefinition->setArguments([
            new Reference('sonata.block.manager'),
            new Parameter('sonata_block.cache_blocks'),
            new Reference('sonata.block.renderer'),
            new Reference('sonata.block.context_manager'),
            new Reference('event_dispatcher'),
            new Reference('sonata.cache.manager', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            new Reference('sonata.block.cache.handler', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ]);

        $blockContextManagerDefinition = $container->getDefinition('sonata.block.context_manager.default');
        $blockContextManagerDefinition->setArguments([
            new Reference('sonata.block.loader.chain'),
            new Reference('sonata.block.manager'),
            new Parameter('sonata_block.cache_blocks'),
            new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
        ]);
    }
}
