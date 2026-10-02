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

namespace Sonata\BlockBundle\Tests\Exception\Strategy;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Exception\Filter\FilterInterface;
use Sonata\BlockBundle\Exception\Renderer\RendererInterface;
use Sonata\BlockBundle\Exception\Strategy\StrategyManager;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the Exception Strategy Manager.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class StrategyManagerTest extends TestCase
{
    /**
     * @var Stub&RendererInterface
     */
    private RendererInterface $renderer1;

    /**
     * @var Stub&RendererInterface
     */
    private RendererInterface $renderer2;

    /**
     * @var Stub&FilterInterface
     */
    private FilterInterface $filter1;

    /**
     * @var Stub&FilterInterface
     */
    private FilterInterface $filter2;

    protected function setUp(): void
    {
        $this->renderer1 = static::createStub(RendererInterface::class);
        $this->renderer2 = static::createStub(RendererInterface::class);
        $this->filter1 = static::createStub(FilterInterface::class);
        $this->filter2 = static::createStub(FilterInterface::class);
    }

    public function testGetBlockRendererWithExisting(): void
    {
        $block = $this->createBlock('block.type1');

        $renderer = $this->createManager()->getBlockRenderer($block);
        static::assertSame($this->renderer2, $renderer, 'Should return the block type1 renderer');
    }

    public function testGetBlockRendererWithNonExisting(): void
    {
        $block = $this->createBlock('block.other_type');

        $renderer = $this->createManager()->getBlockRenderer($block);
        static::assertSame($this->renderer1, $renderer, 'Should return the default renderer');
    }

    public function testGetBlockFilterWithExisting(): void
    {
        $block = $this->createBlock('block.type1');

        $filter = $this->createManager()->getBlockFilter($block);
        static::assertSame($this->filter2, $filter, 'Should return the block type1 filter');
    }

    public function testGetBlockFilterWithNonExisting(): void
    {
        $block = $this->createBlock('block.other_type');

        $filter = $this->createManager()->getBlockFilter($block);
        static::assertSame($this->filter1, $filter, 'Should return the default filter');
    }

    public function testHandleExceptionWithKeepNoneFilter(): void
    {
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(static::once())->method('handle')->willReturn(false);
        $this->filter1 = $filter1;

        $renderer1 = $this->createMock(RendererInterface::class);
        $renderer1->expects(static::never())->method('render');
        $this->renderer1 = $renderer1;

        $exception = new \Exception();
        $block = $this->createBlock('block.other_type');

        $response = $this->createManager()->handleException($exception, $block);
        static::assertInstanceOf(Response::class, $response, 'should return a response object');
    }

    public function testHandleExceptionWithKeepAllFilter(): void
    {
        $rendererResponse = new Response();
        $rendererResponse->setContent('renderer response');

        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects(static::once())->method('handle')->willReturn(true);
        $this->filter1 = $filter1;

        $renderer1 = $this->createMock(RendererInterface::class);
        $renderer1->expects(static::once())->method('render')->willReturn($rendererResponse);
        $this->renderer1 = $renderer1;

        $exception = new \Exception();
        $block = $this->createBlock('block.other_type');

        $response = $this->createManager()->handleException($exception, $block);
        static::assertSame('renderer response', $response->getContent(), 'should return the renderer response');
    }

    private function createManager(): StrategyManager
    {
        $container = $this->createContainer([
            'service.renderer1' => $this->renderer1,
            'service.renderer2' => $this->renderer2,
            'service.filter1' => $this->filter1,
            'service.filter2' => $this->filter2,
        ]);

        $manager = new StrategyManager(
            $container,
            ['filter1' => 'service.filter1', 'filter2' => 'service.filter2'],
            ['renderer1' => 'service.renderer1', 'renderer2' => 'service.renderer2'],
            ['block.type1' => 'filter2'],
            ['block.type1' => 'renderer2']
        );

        $manager->setDefaultFilter('filter1');
        $manager->setDefaultRenderer('renderer1');

        return $manager;
    }

    /**
     * Returns a block model stub with given type.
     *
     * @return BlockInterface&Stub
     */
    private function createBlock(string $type): BlockInterface
    {
        $block = static::createStub(BlockInterface::class);
        $block->method('getType')->willReturn($type);

        return $block;
    }

    /**
     * Returns a container stub with defined services.
     *
     * @param array<string, mixed> $services
     *
     * @return ContainerInterface&Stub
     */
    private function createContainer(array $services = []): ContainerInterface
    {
        $map = [];
        foreach ($services as $name => $service) {
            $map[] = [$name, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $service];
        }

        $container = static::createStub(ContainerInterface::class);
        $container->method('get')->willReturnMap($map);

        return $container;
    }
}
