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

namespace Sonata\BlockBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\BlockBundle\DependencyInjection\SonataBlockExtension;

class SonataBlockExtensionTest extends AbstractExtensionTestCase
{
    public function testLoadDefault(): void
    {
        $this->setParameter('kernel.bundles', []);
        $this->load();

        $this->assertContainerBuilderHasService('sonata.block.service.container');
        $this->assertContainerBuilderHasService('sonata.block.service.empty');
        $this->assertContainerBuilderHasService('sonata.block.service.text');
        $this->assertContainerBuilderHasService('sonata.block.service.rss');
        $this->assertContainerBuilderHasService('sonata.block.service.template');

        $this->assertContainerBuilderNotHasService('sonata.block.service.menu');
    }

    public function testLoadWithKnpMenuBundle(): void
    {
        $this->setParameter('kernel.bundles', ['KnpMenuBundle' => true]);
        $this->load();

        $this->assertContainerBuilderHasService('sonata.block.service.menu');
    }

    protected function getContainerExtensions(): array
    {
        return [
            new SonataBlockExtension(),
        ];
    }
}
