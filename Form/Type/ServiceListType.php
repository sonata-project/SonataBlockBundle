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

use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

class ServiceListType extends ChoiceType
{
    protected $manager;

    protected $contexts;

    /**
     * @param \Sonata\BlockBundle\Block\BlockServiceManagerInterface $manager
     * @param array $contexts
     */
    public function __construct(BlockServiceManagerInterface $manager, array $contexts = array())
    {
        $this->manager  = $manager;
        $this->contexts = $contexts;
    }

    /**
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        $multiple = isset($options['multiple']) && $options['multiple'];
        $expanded = isset($options['expanded']) && $options['expanded'];

        if (!isset($options['context'])) {
            throw new FormException('Please define a context option');
        }

        if (!isset($this->contexts[$options['context']])) {
            throw new FormException('Invalid context');
        }

        return array(
            'context'           => false,
            'multiple'          => false,
            'expanded'          => false,
            'choice_list'       => null,
            'choices'           => $this->getBlockTypes($options['context']),
            'preferred_choices' => array(),
            'empty_data'        => $multiple || $expanded ? array() : '',
            'empty_value'       => $multiple || $expanded || !isset($options['empty_value']) ? null : '',
            'error_bubbling'    => false,
        );
    }

    /**
     * @return array
     */
    protected function getBlockTypes($context)
    {
        $types = array();
        foreach ($this->contexts[$context] as $service) {
            $types[$service] = sprintf('%s - %s', $this->manager->getService($service)->getName(), $service);
        }

        return $types;
    }
}