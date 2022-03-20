<?php

declare(strict_types=1);

use Sonata\BlockBundle\Block\Service\ContainerBlockService;
use Sonata\BlockBundle\Block\Service\EmptyBlockService;
use Sonata\BlockBundle\Block\Service\RssBlockService;
use Sonata\BlockBundle\Block\Service\TemplateBlockService;
use Sonata\BlockBundle\Block\Service\TextBlockService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $services = $containerConfigurator->services();

    $services->set('sonata.block.service.container', ContainerBlockService::class)
        ->tag('sonata.block')
        ->args([
            new ReferenceConfigurator('twig'),
        ]);

    $services->set('sonata.block.service.empty', EmptyBlockService::class)
        ->tag('sonata.block')
        ->args([
            new ReferenceConfigurator('twig'),
        ]);

    $services->set('sonata.block.service.text', TextBlockService::class)
        ->tag('sonata.block')
        ->args([
            new ReferenceConfigurator('twig'),
        ]);

    $services->set('sonata.block.service.rss', RssBlockService::class)
        ->tag('sonata.block')
        ->args([
            new ReferenceConfigurator('twig'),
        ]);

    $services->set('sonata.block.service.template', TemplateBlockService::class)
        ->tag('sonata.block')
        ->args([
            new ReferenceConfigurator('twig'),
        ]);
};
