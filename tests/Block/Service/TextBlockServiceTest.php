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

namespace Sonata\BlockBundle\Tests\Block\Service;

use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\TextBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TextBlockServiceTest extends BlockServiceTestCase
{
    public function testService(): void
    {
        $service = new TextBlockService($this->twig);

        $block = new Block();
        $block->setType('core.text');
        $block->setSettings([
            'content' => 'my text',
        ]);

        $optionResolver = new OptionsResolver();
        $service->configureSettings($optionResolver);

        $blockContext = new BlockContext($block, $optionResolver->resolve($block->getSettings()));

        $formMapper = $this->createMock(FormMapper::class);
        $formMapper->expects(static::exactly(2))->method('add');

        $service->configureCreateForm($formMapper, $block);
        $service->configureEditForm($formMapper, $block);

        $service->execute($blockContext);
    }

    public function testExecute(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockContext = $this->createMock(BlockContextInterface::class);
        $blockContext->method('getTemplate')
            ->willReturn('@SonataBlock/Block/block_core_text.html.twig');
        $blockContext->method('getSettings')
            ->willReturn(['content' => 'foo']);
        $blockContext->method('getBlock')
            ->willReturn($block);

        $this->twig->expects(static::once())->method('render')
            ->with('@SonataBlock/Block/block_core_text.html.twig', [
                'block' => $block,
                'settings' => ['content' => 'foo'],
            ]);

        $service = new TextBlockService($this->twig);
        $response = $service->execute($blockContext);

        static::assertInstanceOf(Response::class, $response);
    }
}
