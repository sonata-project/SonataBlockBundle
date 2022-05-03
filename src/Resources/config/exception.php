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

use Sonata\BlockBundle\Exception\BlockExceptionInterface;
use Sonata\BlockBundle\Exception\Filter\DebugOnlyFilter;
use Sonata\BlockBundle\Exception\Filter\IgnoreClassFilter;
use Sonata\BlockBundle\Exception\Filter\KeepAllFilter;
use Sonata\BlockBundle\Exception\Filter\KeepNoneFilter;
use Sonata\BlockBundle\Exception\Renderer\InlineDebugRenderer;
use Sonata\BlockBundle\Exception\Renderer\InlineRenderer;
use Sonata\BlockBundle\Exception\Renderer\MonkeyThrowRenderer;
use Sonata\BlockBundle\Exception\Strategy\StrategyManager;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('sonata.block.exception.strategy.manager', StrategyManager::class)
        ->args([
            service('service_container'),
            abstract_arg('filters array'),
            abstract_arg('renderers array'),
            abstract_arg('block filters array'),
            abstract_arg('block renderers array'),
        ]);

    $services->set('sonata.block.exception.filter.keep_none', KeepNoneFilter::class)
        ->public();

    $services->set('sonata.block.exception.filter.keep_all', KeepAllFilter::class)
        ->public();

    $services->set('sonata.block.exception.filter.debug_only', DebugOnlyFilter::class)
        ->public()
        ->args([
            param('kernel.debug'),
        ]);

    $services->set('sonata.block.exception.filter.ignore_block_exception', IgnoreClassFilter::class)
        ->public()
        ->args([
            BlockExceptionInterface::class,
        ]);

    $services->set('sonata.block.exception.renderer.inline', InlineRenderer::class)
        ->public()
        ->args([
            service('twig'),
            '@SonataBlock/Block/block_exception.html.twig',
        ]);

    $services->set('sonata.block.exception.renderer.inline_debug', InlineDebugRenderer::class)
        ->public()
        ->args([
            service('twig'),
            '@SonataBlock/Block/block_exception_debug.html.twig',
            param('kernel.debug'),
            true,
        ]);

    $services->set('sonata.block.exception.renderer.throw', MonkeyThrowRenderer::class)
        ->public();
};
