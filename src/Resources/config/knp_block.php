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

use Sonata\BlockBundle\Block\Service\MenuBlockService;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('sonata.block.service.menu', MenuBlockService::class)
        ->tag('sonata.block')
        ->args([
            service('twig'),
            service('knp_menu.menu_provider'),
            service('sonata.block.menu.registry'),
        ]);
};
