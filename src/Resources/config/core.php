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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\BlockBundle\Block\BlockContextManager;
use Sonata\BlockBundle\Block\BlockLoaderChain;
use Sonata\BlockBundle\Block\BlockRenderer;
use Sonata\BlockBundle\Block\BlockServiceManager;
use Sonata\BlockBundle\Block\Loader\ServiceLoader;
use Sonata\BlockBundle\Menu\MenuRegistry;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\BlockBundle\Twig\Extension\BlockExtension;
use Sonata\BlockBundle\Twig\GlobalVariables;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('sonata.block.manager', BlockServiceManager::class)
        ->public()
        ->args([
            abstract_arg('container of block services'),
            param('sonata.block.container.types'),
        ]);

    $services->set('sonata.block.menu.registry', MenuRegistry::class)
        ->public();

    $services->set('sonata.block.context_manager.default', BlockContextManager::class)
        ->public()
        ->args([
            service('sonata.block.loader.chain'),
            service('sonata.block.manager'),
            service('logger')->nullOnInvalid(),
        ]);

    $services->set('sonata.block.renderer.default', BlockRenderer::class)
        ->public()
        ->args([
            service('sonata.block.manager'),
            service('sonata.block.exception.strategy.manager'),
            service('logger')->nullOnInvalid(),
        ]);

    $services->set('sonata.block.twig.extension', BlockExtension::class)
        ->tag('twig.extension')
        ->args([
            service('sonata.block.templating.helper'),
        ]);

    $services->set('sonata.block.templating.helper', BlockHelper::class)
        ->tag('twig.runtime')
        ->args([
            service('sonata.block.renderer'),
            service('sonata.block.context_manager'),
            service('event_dispatcher'),
            service('debug.stopwatch')->nullOnInvalid(),
        ]);

    $services->set('sonata.block.loader.chain', BlockLoaderChain::class)
        ->args([
            abstract_arg('loaders array'),
        ]);

    $services->set('sonata.block.loader.service', ServiceLoader::class)
        ->tag('sonata.block.loader')
        ->args([
            abstract_arg('types array'),
        ]);

    $services->set('sonata.block.twig.global', GlobalVariables::class)
        ->args([
            abstract_arg('templates array'),
        ]);

    $services->alias(BlockHelper::class, 'sonata.block.templating.helper');
};
