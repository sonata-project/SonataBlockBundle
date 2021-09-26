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

use PHPUnit\Framework\MockObject\MockObject;
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
     * @var StrategyManager
     */
    private $manager;

    /**
     * @var MockObject&ContainerInterface
     */
    private $container;

    /**
     * @var array<string, string>
     */
    private $filters = [];

    /**
     * @var array<string, string>
     */
    private $renderers = [];

    /**
     * @var array<string, string>
     */
    private $blockFilters = [];

    /**
     * @var array<string, string>
     */
    private $blockRenderers = [];

    /**
     * @var MockObject&RendererInterface
     */
    private $renderer1;

    /**
     * @var MockObject&RendererInterface
     */
    private $renderer2;

    /**
     * @var MockObject&FilterInterface
     */
    private $filter1;

    /**
     * @var MockObject&FilterInterface
     */
    private $filter2;

    protected function setUp(): void
    {
        $this->renderer1 = $this->createMock(RendererInterface::class);
        $this->renderer2 = $this->createMock(RendererInterface::class);
        $this->filter1 = $this->createMock(FilterInterface::class);
        $this->filter2 = $this->createMock(FilterInterface::class);

        $this->container = $this->getMockContainer([
            'service.renderer1' => $this->renderer1,
            'service.renderer2' => $this->renderer2,
            'service.filter1' => $this->filter1,
            'service.filter2' => $this->filter2,
        ]);

        $this->renderers = [];
        $this->renderers['renderer1'] = 'service.renderer1';
        $this->renderers['renderer2'] = 'service.renderer2';

        $this->filters = [];
        $this->filters['filter1'] = 'service.filter1';
        $this->filters['filter2'] = 'service.filter2';

        $this->blockFilters = ['block.type1' => 'filter2'];
        $this->blockRenderers = ['block.type1' => 'renderer2'];

        $this->manager = new StrategyManager($this->container, $this->filters, $this->renderers, $this->blockFilters, $this->blockRenderers);

        $this->manager->setDefaultFilter('filter1');
        $this->manager->setDefaultRenderer('renderer1');
    }

    public function testGetBlockRendererWithExisting(): void
    {
        $block = $this->getMockBlock('block.type1');

        $renderer = $this->manager->getBlockRenderer($block);
        static::assertSame($this->renderer2, $renderer, 'Should return the block type1 renderer');
    }

    public function testGetBlockRendererWithNonExisting(): void
    {
        $block = $this->getMockBlock('block.other_type');

        $renderer = $this->manager->getBlockRenderer($block);
        static::assertSame($this->renderer1, $renderer, 'Should return the default renderer');
    }

    public function testGetBlockFilterWithExisting(): void
    {
        $block = $this->getMockBlock('block.type1');

        $filter = $this->manager->getBlockFilter($block);
        static::assertSame($this->filter2, $filter, 'Should return the block type1 filter');
    }

    public function testGetBlockFilterWithNonExisting(): void
    {
        $block = $this->getMockBlock('block.other_type');

        $filter = $this->manager->getBlockFilter($block);
        static::assertSame($this->filter1, $filter, 'Should return the default filter');
    }

    public function testHandleExceptionWithKeepNoneFilter(): void
    {
        $this->filter1->expects(static::once())->method('handle')->willReturn(false);

        $exception = new \Exception();
        $block = $this->getMockBlock('block.other_type');

        $response = $this->manager->handleException($exception, $block);
        static::assertInstanceOf(Response::class, $response, 'should return a response object');
    }

    public function testHandleExceptionWithKeepAllFilter(): void
    {
        $rendererResponse = new Response();
        $rendererResponse->setContent('renderer response');

        $this->filter1->expects(static::once())->method('handle')->willReturn(true);
        $this->renderer1->expects(static::once())->method('render')->willReturn($rendererResponse);

        $exception = new \Exception();
        $block = $this->getMockBlock('block.other_type');

        $response = $this->manager->handleException($exception, $block);
        static::assertSame('renderer response', $response->getContent(), 'should return the renderer response');
    }

    /**
     * Returns a mock block model with given type.
     *
     * @return BlockInterface&MockObject
     */
    private function getMockBlock(string $type): BlockInterface
    {
        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::any())->method('getType')->willReturn($type);

        return $block;
    }

    /**
     * Returns a mock container with defined services.
     *
     * @param array<string, mixed> $services
     *
     * @return ContainerInterface&MockObject
     */
    private function getMockContainer(array $services = []): ContainerInterface
    {
        $map = [];
        foreach ($services as $name => $service) {
            $map[] = [$name, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $service];
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(static::any())->method('get')->willReturnMap($map);

        return $container;
    }
}
