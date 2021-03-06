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

namespace Sonata\BlockBundle;

use Sonata\BlockBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\BlockBundle\DependencyInjection\Compiler\TweakCompilerPass;
use Sonata\CoreBundle\Form\FormHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @final since sonata-project/block-bundle 3.0
 */
class SonataBlockBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TweakCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());

        $this->registerFormMapping();
    }

    public function boot()
    {
        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     *
     * NEXT_MAJOR: remove this method
     */
    public function registerFormMapping()
    {
        if (class_exists(FormHelper::class)) {
            FormHelper::registerFormTypeMapping([
                'sonata_block_service_choice' => 'Sonata\BlockBundle\Form\Type\ServiceListType',
                'sonata_type_container_template_choice' => 'Sonata\BlockBundle\Form\Type\ContainerTemplateType',
            ]);
        }
    }
}
