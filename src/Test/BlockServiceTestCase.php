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

namespace Sonata\BlockBundle\Test;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\BlockContextManager;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockLoaderInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Twig\Environment;

/**
 * Abstract test class for block service tests.
 *
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
abstract class BlockServiceTestCase extends TestCase
{
    /**
     * @var MockObject&BlockServiceManagerInterface
     */
    protected $blockServiceManager;

    /**
     * @var BlockContextManagerInterface
     */
    protected $blockContextManager;

    /**
     * @var MockObject&Environment
     */
    protected $twig;

    /**
     * @var MockObject&BlockInterface
     */
    protected $block;

    protected function setUp(): void
    {
        $blockLoader = $this->createMock(BlockLoaderInterface::class);
        $this->blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $this->blockContextManager = new BlockContextManager($blockLoader, $this->blockServiceManager);
        $this->twig = $this->createMock(Environment::class);
        $this->block = $this->createMock(BlockInterface::class);
    }

    /**
     * Create a mocked block service.
     */
    protected function getBlockContext(BlockServiceInterface $blockService): BlockContextInterface
    {
        $this->blockServiceManager->expects(static::once())->method('get')->willReturn($blockService);
        $this->block->expects(static::once())->method('getSettings')->willReturn([]);

        return $this->blockContextManager->get($this->block);
    }

    /**
     * Asserts that the block settings have the expected values.
     *
     * @param array<string, mixed> $expected Expected settings
     */
    protected function assertSettings(array $expected, BlockContextInterface $blockContext): void
    {
        $completeExpectedOptions = $expected + [
            'use_cache' => true,
            'extra_cache_keys' => [],
            'attr' => [],
            'template' => null,
            'ttl' => 0,
        ];

        ksort($completeExpectedOptions);
        $blockSettings = $blockContext->getSettings();
        ksort($blockSettings);

        static::assertSame($completeExpectedOptions, $blockSettings);
    }
}
