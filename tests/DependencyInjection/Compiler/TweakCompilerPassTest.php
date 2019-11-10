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

namespace Sonata\BlockBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\DependencyInjection\Compiler\TweakCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class TweakCompilerPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * Setup test object.
     */
    public function setUp()
    {
        $this->container = new ContainerBuilder();

        $this->container->setDefinition('sonata.block.menu.registry', $this->createMock(Definition::class));
        $this->container->setDefinition('sonata.block.loader.chain', $this->createMock(Definition::class));
        $this->container->setDefinition('sonata.block.context_manager', $this->createMock(Definition::class));
        $this->container->setDefinition('sonata.block.loader.service', $this->createMock(Definition::class));

        $this->container->setParameter('sonata_block.blocks', []);
        $this->container->setParameter('sonata_blocks.block_types', []);
        $this->container->setParameter('sonata_block.cache_blocks', []);
        $this->container->setParameter('sonata_blocks.default_contexts', []);
        $this->container->setParameter('sonata_block.blocks_by_class', []);
    }

    public function testProcessAutowired()
    {
        $blockDefinition = new Definition(null, ['acme.block.service']);
        $blockDefinition->addTag('sonata.block');
        $blockDefinition->setAutoconfigured(true);

        $managerDefinition = $this->createMock(Definition::class);
        $managerDefinition->expects($this->once())->method('addMethodCall')->with('add', ['acme.block.service', 'acme.block.service', []]);

        $this->container->setDefinition('acme.block.service', $blockDefinition);
        $this->container->setDefinition('sonata.block.manager', $managerDefinition);

        $pass = new TweakCompilerPass();
        $pass->process($this->container);
    }

    public function testProcessSameBlockId()
    {
        /** @var Definition $blockDefinition */
        $blockDefinition = new Definition(null, ['acme.block.service']);
        $blockDefinition->addTag('sonata.block');

        $managerDefinition = $this->createMock(Definition::class);
        $managerDefinition->expects($this->once())->method('addMethodCall')->with('add', ['acme.block.service', 'acme.block.service', []]);

        $this->container->setDefinition('acme.block.service', $blockDefinition);
        $this->container->setDefinition('sonata.block.manager', $managerDefinition);

        $pass = new TweakCompilerPass();
        $pass->process($this->container);
    }

    /**
     * @group legacy
     */
    public function testProcessDifferentBlockId()
    {
        /** @var Definition $blockDefinition */
        $blockDefinition = new Definition(null, ['acme.block.service.name']);
        $blockDefinition->addTag('sonata.block');

        $managerDefinition = $this->createMock(Definition::class);
        $managerDefinition->expects($this->once())->method('addMethodCall')->with('add', ['acme.block.service', 'acme.block.service', []]);

        $this->container->setDefinition('acme.block.service', $blockDefinition);
        $this->container->setDefinition('sonata.block.manager', $managerDefinition);

        $pass = new TweakCompilerPass();
        $pass->process($this->container);
    }

    public function blockIds(): \Generator
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
    }

    /**
     * @dataProvider blockIds
     */
    public function testProcessEmptyBlockId($blockId)
    {
        /** @var Definition $blockDefinition */
        $blockDefinition = new Definition(null, [$blockId]);
        $blockDefinition->addTag('sonata.block');

        $managerDefinition = $this->createMock(Definition::class);
        $managerDefinition->expects($this->once())->method('addMethodCall')->with('add', ['acme.block.service', 'acme.block.service', []]);

        $this->container->setDefinition('acme.block.service', $blockDefinition);
        $this->container->setDefinition('sonata.block.manager', $managerDefinition);

        $pass = new TweakCompilerPass();
        $pass->process($this->container);

        $this->assertSame('acme.block.service', $blockDefinition->getArgument(0));
    }
}
