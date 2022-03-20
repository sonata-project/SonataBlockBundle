<?php

declare(strict_types=1);

use Sonata\BlockBundle\Command\DebugBlocksCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $services = $containerConfigurator->services();

    $services->set('sonata.block.command.debug_blocks', DebugBlocksCommand::class)
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.block.manager'),
        ]);
};
