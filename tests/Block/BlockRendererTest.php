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

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockRenderer;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Exception\Strategy\StrategyManager;

/**
 * Unit test of BlockRenderer class.
 */
final class BlockRendererTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|BlockServiceManagerInterface
     */
    protected $blockServiceManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StrategyManager
     */
    protected $exceptionStrategyManager;

    /**
     * @var BlockRenderer
     */
    protected $renderer;

    /**
     * Setup test object.
     */
    protected function setUp(): void
    {
        $this->blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $this->exceptionStrategyManager = $this->createMock('Sonata\BlockBundle\Exception\Strategy\StrategyManagerInterface');
        $this->logger = $this->createMock('Psr\Log\LoggerInterface');

        $this->renderer = new BlockRenderer($this->blockServiceManager, $this->exceptionStrategyManager, $this->logger);
    }

    /**
     * Test rendering a block without errors.
     */
    public function testRenderWithoutErrors()
    {
        // GIVEN

        // mock a block service that returns a response
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $service = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $service->expects($this->once())->method('load');
        $service->expects($this->once())->method('execute')->willReturn($response);
        $this->blockServiceManager->expects($this->once())->method('get')->willReturn($service);

        // mock a block object
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $blockContext = new BlockContext($block);

        // WHEN
        $result = $this->renderer->render($blockContext);

        // THEN
        $this->assertSame($response, $result, 'Should return the response from the block service');
    }

    /**
     * Test rendering a block that returns a wrong response.
     */
    public function testRenderWithWrongResponse()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('A block service must return a Response object');

        // GIVEN

        // mock a block service that returns a string response
        $service = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $service->expects($this->once())->method('load');
        $service->expects($this->once())->method('execute')->willReturn('wrong response');

        $this->blockServiceManager->expects($this->once())->method('get')->willReturn($service);

        // mock the exception strategy manager to rethrow the exception
        $this->exceptionStrategyManager->expects($this->once())
            ->method('handleException')
            ->willReturnCallback(static function ($e) {
                throw $e;
            });

        // mock the logger to ensure a crit message is logged
        $this->logger->expects($this->once())->method('error');

        // mock a block object
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $blockContext = new BlockContext($block);

        // WHEN
        $this->renderer->render($blockContext);

        // THEN
        // exception thrown
    }

    /**
     * Test rendering a block that throws an exception.
     */
    public function testRenderBlockWithException()
    {
        // GIVEN

        // mock a block service that throws an user exception
        $service = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $service->expects($this->once())->method('load');

        $exception = $this->createMock('\Exception');
        $service->expects($this->once())
            ->method('execute')
            ->willReturnCallback(static function () use ($exception) {
                throw $exception;
            });

        $this->blockServiceManager->expects($this->once())->method('get')->willReturn($service);

        // mock the exception strategy manager to return a response when given the correct exception
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $this->exceptionStrategyManager->expects($this->once())
            ->method('handleException')
            ->with($this->equalTo($exception))
            ->willReturn($response);

        // mock the logger to ensure a crit message is logged
        $this->logger->expects($this->once())->method('error');

        // mock a block object
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');
        $blockContext = new BlockContext($block);

        // WHEN
        $result = $this->renderer->render($blockContext);

        // THEN
        $this->assertSame($response, $result, 'Should return the response provider by the exception manager');
    }
}
