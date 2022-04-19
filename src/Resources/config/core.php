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

use Sonata\BlockBundle\Block\BlockContextManager;
use Sonata\BlockBundle\Block\BlockLoaderChain;
use Sonata\BlockBundle\Block\BlockRenderer;
use Sonata\BlockBundle\Block\BlockServiceManager;
use Sonata\BlockBundle\Block\Loader\ServiceLoader;
use Sonata\BlockBundle\Cache\HttpCacheHandler;
use Sonata\BlockBundle\Cache\NoopHttpCacheHandler;
use Sonata\BlockBundle\Menu\MenuRegistry;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\BlockBundle\Twig\Extension\BlockExtension;
use Sonata\BlockBundle\Twig\GlobalVariables;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $services = $containerConfigurator->services();

    $services->set('sonata.block.manager', BlockServiceManager::class)
        ->public()
        ->args([
            new ReferenceConfigurator('service_container'),
            '%kernel.debug%',
            (new ReferenceConfigurator('logger'))->nullOnInvalid(),
        ]);

    $services->set('sonata.block.menu.registry', MenuRegistry::class)
        ->public();

    $services->set('sonata.block.context_manager.default', BlockContextManager::class)
        ->public()
        ->args([
            new ReferenceConfigurator('sonata.block.loader.chain'),
            new ReferenceConfigurator('sonata.block.manager'),
            (new ReferenceConfigurator('logger'))->nullOnInvalid(),
        ]);

    $services->set('sonata.block.renderer.default', BlockRenderer::class)
        ->public()
        ->args([
            new ReferenceConfigurator('sonata.block.manager'),
            new ReferenceConfigurator('sonata.block.exception.strategy.manager'),
            (new ReferenceConfigurator('logger'))->nullOnInvalid(),
        ]);

    $services->set('sonata.block.twig.extension', BlockExtension::class)
        ->tag('twig.extension')
        ->args([
            new ReferenceConfigurator('sonata.block.templating.helper'),
        ]);

    $services->set('sonata.block.templating.helper', BlockHelper::class)
        ->tag('twig.runtime')
        ->args([
            new ReferenceConfigurator('sonata.block.manager'),
            new ReferenceConfigurator('sonata.block.renderer'),
            new ReferenceConfigurator('sonata.block.context_manager'),
            new ReferenceConfigurator('event_dispatcher'),
            (new ReferenceConfigurator('debug.stopwatch'))->nullOnInvalid(),
            (new ReferenceConfigurator('sonata.cache.manager'))->nullOnInvalid(),
            (new ReferenceConfigurator('sonata.block.cache.handler'))->nullOnInvalid(),
        ]);

    $services->set('sonata.block.loader.chain', BlockLoaderChain::class)
        ->args([
            [], // loaders
        ]);

    $services->set('sonata.block.loader.service', ServiceLoader::class)
        ->tag('sonata.block.loader')
        ->args([
            [], // types
        ]);

    $services->set('sonata.block.twig.global', GlobalVariables::class)
        ->args([
            [], // templates
        ]);

    $services->set('sonata.block.cache.handler.default', HttpCacheHandler::class);

    $services->set('sonata.block.cache.handler.noop', NoopHttpCacheHandler::class);

    $services->alias(BlockHelper::class, 'sonata.block.templating.helper');
};
