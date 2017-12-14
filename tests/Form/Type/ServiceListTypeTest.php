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
use Sonata\BlockBundle\Form\Type\ServiceListType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceListTypeTest extends TestCase
{
    public function testFormType(): void
    {
        $blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');

        $type = new ServiceListType($blockServiceManager);

        $this->assertEquals('sonata_block_service_choice', $type->getName());
        $this->assertEquals(ChoiceType::class, $type->getParent());
    }

    public function testOptionsWithInvalidContext(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\MissingOptionsException::class);

        $blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');

        $type = new ServiceListType($blockServiceManager);

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $resolver->resolve();
    }

    public function testOptionWithValidContext(): void
    {
        $blockService = $this->createMock('Sonata\BlockBundle\Block\BlockServiceInterface');
        $blockService->expects($this->once())->method('getName')->will($this->returnValue('value'));

        $blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $blockServiceManager
            ->expects($this->once())
            ->method('getServicesByContext')
            ->with($this->equalTo('cms'))
            ->will($this->returnValue(['my.service.code' => $blockService]));

        $type = new ServiceListType($blockServiceManager, [
            'cms' => ['my.service.code'],
        ]);

        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve([
            'context' => 'cms',
        ]);

        $expected = [
            'multiple' => false,
            'expanded' => false,
            'choices' => [
                'my.service.code' => 'value - my.service.code',
            ],
            'preferred_choices' => [],
            'empty_data' => '',
            'empty_value' => null,
            'error_bubbling' => false,
            'context' => 'cms',
            'include_containers' => false,
        ];

        $this->assertEquals($expected, $options);
    }
}
