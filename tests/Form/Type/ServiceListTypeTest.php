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

namespace Sonata\BlockBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Form\Type\ServiceListType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ServiceListTypeTest extends TestCase
{
    public function testFormType(): void
    {
        $type = new ServiceListType(
            $this->createMock(BlockServiceManagerInterface::class)
        );

        static::assertSame('sonata_block_service_choice', $type->getBlockPrefix());
        static::assertSame(ChoiceType::class, $type->getParent());
    }

    public function testOptionsWithInvalidContext(): void
    {
        $this->expectException(MissingOptionsException::class);

        $type = new ServiceListType(
            $this->createMock(BlockServiceManagerInterface::class)
        );

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolver->resolve();
    }

    /**
     * @group legacy
     */
    public function testOptionWithValidContext(): void
    {
        $blockService = $this->createMock(BlockServiceInterface::class);

        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockServiceManager
            ->expects(static::once())
            ->method('getServicesByContext')
            ->with(static::equalTo('cms'))
            ->willReturn(['my.service.code' => $blockService]);

        $type = new ServiceListType($blockServiceManager);

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve([
            'context' => 'cms',
        ]);

        $expected = [
            'multiple' => false,
            'expanded' => false,
            'preferred_choices' => [],
            'error_bubbling' => false,
            'include_containers' => false,
            'context' => 'cms',
            'choices' => [
                'my.service.code' => 'my.service.code',
            ],
            'empty_data' => '',
            'empty_value' => null,
        ];

        static::assertSame($expected, $options);
    }
}
