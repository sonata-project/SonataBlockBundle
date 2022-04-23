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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SonataBlockBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TweakCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
    }
}
