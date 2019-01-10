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

namespace Sonata\BlockBundle\Form\Mapper;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Christian Gripp <mail@core23.de>
 */
interface FormMapper
{
    /**
     * @param string $name
     * @param mixed  $type
     *
     * @return FormBuilderInterface
     */
    public function create($name, $type = null, array $options = []);

    /**
     * @param array $keys field names
     *
     * @return self
     */
    public function reorder(array $keys);

    /**
     * @param FormBuilderInterface|string $name
     * @param string                      $type
     *
     * @return self
     */
    public function add($name, $type = null, array $options = []);

    /**
     * @param string $key
     *
     * @return self
     */
    public function remove($key);

    /**
     * @return self
     */
    public function setHelps(array $helps = []);

    /**
     * @return self
     */
    public function addHelp($name, $help);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($name);
}
