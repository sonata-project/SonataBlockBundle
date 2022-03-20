<?php

declare(strict_types=1);

use Sonata\BlockBundle\Form\Type\ContainerTemplateType;
use Sonata\BlockBundle\Form\Type\ServiceListType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $services = $containerConfigurator->services();

    $services->set('sonata.block.form.type.block', ServiceListType::class)
        ->tag('form.type', ['alias' => 'sonata_block_service_choice'])
        ->args([
            new ReferenceConfigurator('sonata.block.manager'),
        ]);

    $services->set('sonata.block.form.type.container_template', ContainerTemplateType::class)
        ->tag('form.type', ['alias' => 'sonata_type_container_template_choice'])
        ->args([
            [], // template choices
        ]);
};
