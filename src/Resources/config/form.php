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

use Sonata\BlockBundle\Form\Type\ContainerTemplateType;
use Sonata\BlockBundle\Form\Type\ServiceListType;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('sonata.block.form.type.block', ServiceListType::class)
        ->tag('form.type', ['alias' => 'sonata_block_service_choice'])
        ->args([
            service('sonata.block.manager'),
        ]);

    $services->set('sonata.block.form.type.container_template', ContainerTemplateType::class)
        ->tag('form.type', ['alias' => 'sonata_type_container_template_choice'])
        ->args([
            abstract_arg('template choices array'),
        ]);
};
