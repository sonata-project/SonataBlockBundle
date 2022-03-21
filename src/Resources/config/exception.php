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

use Sonata\BlockBundle\Exception\BlockExceptionInterface;
use Sonata\BlockBundle\Exception\Filter\DebugOnlyFilter;
use Sonata\BlockBundle\Exception\Filter\IgnoreClassFilter;
use Sonata\BlockBundle\Exception\Filter\KeepAllFilter;
use Sonata\BlockBundle\Exception\Filter\KeepNoneFilter;
use Sonata\BlockBundle\Exception\Renderer\InlineDebugRenderer;
use Sonata\BlockBundle\Exception\Renderer\InlineRenderer;
use Sonata\BlockBundle\Exception\Renderer\MonkeyThrowRenderer;
use Sonata\BlockBundle\Exception\Strategy\StrategyManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $services = $containerConfigurator->services();

    $services->set('sonata.block.exception.strategy.manager', StrategyManager::class)
        ->args([
            new ReferenceConfigurator('service_container'),
            [], // filters
            [], // renderers
            [], // block filters
            [], // block renderers
        ]);

    $services->set('sonata.block.exception.filter.keep_none', KeepNoneFilter::class)
        ->public();

    $services->set('sonata.block.exception.filter.keep_all', KeepAllFilter::class)
        ->public();

    $services->set('sonata.block.exception.filter.debug_only', DebugOnlyFilter::class)
        ->public()
        ->args([
            '%kernel.debug%',
        ]);

    $services->set('sonata.block.exception.filter.ignore_block_exception', IgnoreClassFilter::class)
        ->public()
        ->args([
            BlockExceptionInterface::class,
        ]);

    $services->set('sonata.block.exception.renderer.inline', InlineRenderer::class)
        ->public()
        ->args([
            new ReferenceConfigurator('twig'),
            '@SonataBlock/Block/block_exception.html.twig',
        ]);

    $services->set('sonata.block.exception.renderer.inline_debug', InlineDebugRenderer::class)
        ->public()
        ->args([
            new ReferenceConfigurator('twig'),
            '@SonataBlock/Block/block_exception_debug.html.twig',
            '%kernel.debug%',
            true,
        ]);

    $services->set('sonata.block.exception.renderer.throw', MonkeyThrowRenderer::class)
        ->public();
};
