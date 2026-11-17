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

namespace Sonata\BlockBundle\Tests\Block;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\BlockContextManager;
use Sonata\BlockBundle\Block\BlockLoaderInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Tests\Fixtures\Block\__CG__\Sonata\BlockBundle\Model\Block as ProxyBlock;
use Sonata\BlockBundle\Tests\Fixtures\Block\CustomBlock;

final class BlockContextManagerTest extends TestCase
{
    /**
     * @phpstan-param class-string<Block> $settingsClass
     */
    #[DataProvider('provideClassSettingsCases')]
    public function testClassSettings(Block $block, string $settingsClass): void
    {
        $service = $this->createMock(AbstractBlockService::class);
        $service->expects(static::once())->method('configureSettings');

        $serviceManager = $this->createMock(BlockServiceManagerInterface::class);
        $serviceManager->expects(static::once())->method('get')->with($block)->willReturn($service);

        $manager = new BlockContextManager(static::createStub(BlockLoaderInterface::class), $serviceManager);
        $manager->addSettingsByClass(Block::class, ['template' => 'base.html.twig']);
        $manager->addSettingsByClass($settingsClass, ['template' => 'class.html.twig'], true);

        static::assertSame('class.html.twig', $manager->get($block)->getSetting('template'));
    }

    /**
     * @phpstan-return iterable<string, array{Block, class-string<Block>}>
     */
    public static function provideClassSettingsCases(): iterable
    {
        yield 'ordinary block' => [new Block(), Block::class];
        yield 'subclass keeps its own settings' => [new CustomBlock(), CustomBlock::class];
        yield 'Doctrine proxy uses original class settings' => [new ProxyBlock(), Block::class];
    }

    public function testGetWithValidData(): void
    {
        $service = $this->createMock(AbstractBlockService::class);

        $service->expects(static::once())->method('configureSettings');

        $blockLoader = static::createStub(BlockLoaderInterface::class);

        $serviceManager = $this->createMock(BlockServiceManagerInterface::class);
        $serviceManager->expects(static::once())->method('get')->willReturn($service);

        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::once())->method('getSettings')->willReturn([]);

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $settings = ['template' => 'custom.html.twig'];

        $blockContext = $manager->get($block, $settings);

        static::assertInstanceOf(BlockContextInterface::class, $blockContext);

        static::assertSame([
            'attr' => [],
            'template' => 'custom.html.twig',
        ], $blockContext->getSettings());
    }

    public function testGetWithBlockMeta(): void
    {
        $service = $this->createMock(AbstractBlockService::class);

        $service->expects(static::once())->method('configureSettings');

        $blockLoader = $this->createMock(BlockLoaderInterface::class);
        $blockLoader->expects(static::once())->method('load')->willReturn($block = new Block());

        $serviceManager = $this->createMock(BlockServiceManagerInterface::class);
        $serviceManager->expects(static::once())->method('get')->willReturn($service);

        $blockMeta = [
            'type' => 'some_block_id',
        ];

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $settings = ['template' => 'custom.html.twig'];

        $blockContext = $manager->get($blockMeta, $settings);

        static::assertInstanceOf(BlockContextInterface::class, $blockContext);

        static::assertSame([
            'template' => 'custom.html.twig',
        ], $block->getSettings());

        static::assertSame([
            'attr' => [],
            'template' => 'custom.html.twig',
        ], $blockContext->getSettings());
    }

    public function testGetWithSettings(): void
    {
        $service = $this->createMock(AbstractBlockService::class);
        $service->expects(static::once())->method('configureSettings');

        $blockLoader = static::createStub(BlockLoaderInterface::class);

        $serviceManager = $this->createMock(BlockServiceManagerInterface::class);
        $serviceManager->expects(static::once())->method('get')->willReturn($service);

        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::once())->method('getSettings')->willReturn([]);

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $settings = ['template' => 'custom.html.twig'];

        $blockContext = $manager->get($block, $settings);

        static::assertInstanceOf(BlockContextInterface::class, $blockContext);

        static::assertSame([
            'attr' => [],
            'template' => 'custom.html.twig',
        ], $blockContext->getSettings());
    }

    public function testWithInvalidSettings(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(static::once())->method('error');

        $service = $this->createMock(AbstractBlockService::class);
        $service->expects(static::exactly(2))->method('configureSettings');

        $blockLoader = static::createStub(BlockLoaderInterface::class);

        $serviceManager = $this->createMock(BlockServiceManagerInterface::class);
        $serviceManager->expects(static::exactly(2))->method('get')->willReturn($service);

        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::once())->method('getSettings')->willReturn([
            'template' => 'custom.html.twig',
            'attr' => 'shouldBeAnArray',
        ]);
        $block->expects(static::once())->method('getSetting')->with('template')->willReturn('custom.html.twig');

        $manager = new BlockContextManager($blockLoader, $serviceManager, $logger);

        $blockContext = $manager->get($block);

        static::assertInstanceOf(BlockContextInterface::class, $blockContext);
    }
}
