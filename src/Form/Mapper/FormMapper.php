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
    public function create(string $name, ?string $type = null, array $options = []): FormBuilderInterface;

    /**
     * @param string[] $keys field names
     */
    public function reorder(array $keys): self;

    /**
     * @param FormBuilderInterface|string $name
     */
    public function add($name, ?string $type = null, array $options = []): self;

    public function remove(string $key): self;

    public function setHelps(array $helps = []): self;

    public function addHelp(string $name, string $help): self;

    public function has(string $key): bool;

    /**
     * @return mixed
     */
    public function get(string $name);
}
