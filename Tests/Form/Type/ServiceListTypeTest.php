<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Form\Type;

use Sonata\BlockBundle\Form\Type\ServiceListType;
use Sonata\BlockBundle\Tests\PHPUnit_Framework_TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceListTypeTest extends PHPUnit_Framework_TestCase
{
    public function testFormType()
    {
        $blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');

        $type = new ServiceListType($blockServiceManager);

        $this->assertEquals('sonata_block_service_choice', $type->getName());
        $this->assertEquals('choice', $type->getParent());
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     */
    public function testOptionsWithInvalidContext()
    {
        $blockServiceManager = $this->createMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');

        $type = new ServiceListType($blockServiceManager);

        $resolver = new OptionsResolver();

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type->setDefaultOptions($resolver);
        } else {
            $type->configureOptions($resolver);
        }

        $resolver->resolve();
    }

    public function testOptionWithValidContext()
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

        if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type->setDefaultOptions($resolver);
        } else {
            $type->configureOptions($resolver);
        }

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
