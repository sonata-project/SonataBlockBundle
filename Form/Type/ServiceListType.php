<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

class ServiceListType extends ChoiceType
{
    protected $manager;

    public function __construct(BlockServiceManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getDefaultOptions(array $options)
    {
        $multiple = isset($options['multiple']) && $options['multiple'];
        $expanded = isset($options['expanded']) && $options['expanded'];

        return array(
            'multiple' => false,
            'expanded' => false,
            'choice_list' => null,
            'choices' => $this->getBlockTypes(),
            'preferred_choices' => array(),
            'empty_data'        => $multiple || $expanded ? array() : '',
            'empty_value'       => $multiple || $expanded || !isset($options['empty_value']) ? null : '',
            'error_bubbling'    => false,
        );
    }

    public function getBlockTypes()
    {
        $types = array();
        foreach ($this->manager->getBlockServices() as $code => $service) {
            $types[$code] = sprintf('%s - %s', $service->getName(), $code);
        }

        return $types;
    }
}