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

use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

class ServiceListType extends AbstractType
{
    protected $manager;

    protected $contexts;

    /**
     * @param \Sonata\BlockBundle\Block\BlockServiceManagerInterface $manager
     * @param array                                                  $contexts
     */
    public function __construct(BlockServiceManagerInterface $manager, array $contexts = array())
    {
        $this->manager  = $manager;
        $this->contexts = $contexts;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_block_service_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $contexts = $this->contexts;
        $manager = $this->manager;

        $resolver->setDefaults(array(
            'context'           => false,
            'multiple'          => false,
            'expanded'          => false,
            'choices'           => function (Options $options, $previousValue) use ($contexts, $manager) {
                if (!isset($options['context'])) {
                    throw new InvalidArgumentException('Please define a context option');
                }

                if (!isset($contexts[$options['context']])) {
                    throw new InvalidArgumentException('Invalid context');
                }

                $types = array();
                foreach ($contexts[$options['context']] as $service) {
                    $types[$service] = sprintf('%s - %s', $manager->getService($service)->getName(), $service);
                }

                return $types;
            },
            'preferred_choices' => array(),
            'empty_data'        => function (Options $options) {
                $multiple = isset($options['multiple']) && $options['multiple'];
                $expanded = isset($options['expanded']) && $options['expanded'];

                return $multiple || $expanded ? array() : '';
            },
            'empty_value'       => function (Options $options, $previousValue) {
                $multiple = isset($options['multiple']) && $options['multiple'];
                $expanded = isset($options['expanded']) && $options['expanded'];

                return $multiple || $expanded || !isset($previousValue) ? null : '';
            },
            'error_bubbling'    => false,
        ));
    }
}
