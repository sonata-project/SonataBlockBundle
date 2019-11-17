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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Model\BlockInterface;

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
     * @var MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var array
     */
    private $renderers = [];

    /**
     * @var array
     */
    private $blockFilters = [];

    /**
     * @var array
     */
    private $blockRenderers = [];

    /**
     * @var MockObject|RendererInterface
     */
    private $renderer1;

    /**
     * @var MockObject|RendererInterface
     */
    private $renderer2;

    /**
     * @var MockObject|FilterInterface
     */
    private $filter1;

    /**
     * @var MockObject|FilterInterface
     */
    private $filter2;

    /**
     * setup a basic scenario to avoid long test setup.
     */
    public function setUp(): void
    {
        $this->renderer1 = $this->createMock(RendererInterface::class);
        $this->renderer2 = $this->createMock(RendererInterface::class);
        $this->filter1 = $this->createMock(FilterInterface::class);
        $this->filter2 = $this->createMock(FilterInterface::class);

        // setup a mock container which contains our mock renderers and filters
        $this->container = $this->getMockContainer([
            'service.renderer1' => $this->renderer1,
            'service.renderer2' => $this->renderer2,
            'service.filter1' => $this->filter1,
            'service.filter2' => $this->filter2,
        ]);

        // setup 2 mock renderers
        $this->renderers = [];
        $this->renderers['renderer1'] = 'service.renderer1';
        $this->renderers['renderer2'] = 'service.renderer2';

        // setup 2 mock filters
        $this->filters = [];
        $this->filters['filter1'] = 'service.filter1';
        $this->filters['filter2'] = 'service.filter2';

        // setup a specific filter and renderer for "type1" blocks
        $this->blockFilters = ['block.type1' => 'filter2'];
        $this->blockRenderers = ['block.type1' => 'renderer2'];

        // create test object
        $this->manager = new StrategyManager($this->container, $this->filters, $this->renderers, $this->blockFilters, $this->blockRenderers);

        // setup default filters and renderers in manager
        $this->manager->setDefaultFilter('filter1');
        $this->manager->setDefaultRenderer('renderer1');
    }

    /**
     * test getBlockRenderer() with existing block renderer.
     */
    public function testGetBlockRendererWithExisting(): void
    {
        // GIVEN
        $block = $this->getMockBlock('block.type1');

        // WHEN
        $renderer = $this->manager->getBlockRenderer($block);

        // THEN
        $this->assertNotNull($renderer);
        $this->assertSame($this->renderer2, $renderer, 'Should return the block type1 renderer');
    }

    /**
     * test getBlockRenderer() with non existing block renderer.
     */
    public function testGetBlockRendererWithNonExisting(): void
    {
        // GIVEN
        $block = $this->getMockBlock('block.other_type');

        // WHEN
        $renderer = $this->manager->getBlockRenderer($block);

        // THEN
        $this->assertNotNull($renderer);
        $this->assertSame($this->renderer1, $renderer, 'Should return the default renderer');
    }

    /**
     * test getBlockFilter() with an existing block filter.
     */
    public function testGetBlockFilterWithExisting(): void
    {
        // GIVEN
        $block = $this->getMockBlock('block.type1');

        // WHEN
        $filter = $this->manager->getBlockFilter($block);

        // THEN
        $this->assertNotNull($filter);
        $this->assertSame($this->filter2, $filter, 'Should return the block type1 filter');
    }

    /**
     * test getting the default block renderer.
     */
    public function testGetBlockFilterWithNonExisting(): void
    {
        // GIVEN
        $block = $this->getMockBlock('block.other_type');

        // WHEN
        $filter = $this->manager->getBlockFilter($block);

        // THEN
        $this->assertNotNull($filter);
        $this->assertSame($this->filter1, $filter, 'Should return the default filter');
    }

    /**
     * test handleException() with a keep none filter.
     */
    public function testHandleExceptionWithKeepNoneFilter(): void
    {
        // GIVEN
        $this->filter1->expects($this->once())->method('handle')->willReturn(false);

        $exception = new \Exception();
        $block = $this->getMockBlock('block.other_type');

        // WHEN
        $response = $this->manager->handleException($exception, $block);

        // THEN
        $this->assertNotNull($response, 'should return something');
        $this->assertInstanceOf(Response::class, $response, 'should return a response object');
    }

    /**
     * test handleException() with a keep all filter.
     */
    public function testHandleExceptionWithKeepAllFilter(): void
    {
        $rendererResponse = new Response();
        $rendererResponse->setContent('renderer response');
        // GIVEN
        $this->filter1->expects($this->once())->method('handle')->willReturn(true);
        $this->renderer1->expects($this->once())->method('render')->willReturn($rendererResponse);

        $exception = new \Exception();
        $block = $this->getMockBlock('block.other_type');

        // WHEN
        $response = $this->manager->handleException($exception, $block);

        // THEN
        $this->assertNotNull($response, 'should return something');
        $this->assertSame('renderer response', $response->getContent(), 'should return the renderer response');
    }

    /**
     * Returns a mock block model with given type.
     *
     * @param string $type
     *
     * @return \Sonata\BlockBundle\Model\BlockInterface
     */
    protected function getMockBlock($type)
    {
        $block = $this->createMock(BlockInterface::class);
        $block->expects($this->any())->method('getType')->willReturn($type);

        return $block;
    }

    /**
     * Returns a mock container with defined services.
     *
     * @return ContainerInterface
     */
    protected function getMockContainer(array $services = [])
    {
        $map = [];
        foreach ($services as $name => $service) {
            $map[] = [$name, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $service];
        }

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())->method('get')->willReturnMap($map);

        return $container;
    }
}
