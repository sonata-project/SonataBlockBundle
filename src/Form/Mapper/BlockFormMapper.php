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

use Sonata\AdminBundle\Form\FormMapper as AdminFormMapper;
use Symfony\Component\Form\FormBuilderInterface;

final class BlockFormMapper implements FormMapper
{
    /**
     * @var AdminFormMapper
     */
    private $adminFormMapper;

    public function __construct(AdminFormMapper $adminFormMapper)
    {
        $this->adminFormMapper = $adminFormMapper;
    }

    public function create(string $name, ?string $type = null, array $options = []): FormBuilderInterface
    {
        return $this->adminFormMapper->create($name, $type, $options);
    }

    public function reorder(array $keys): FormMapper
    {
        $this->adminFormMapper->reorder($keys);

        return $this;
    }

    public function add($name, ?string $type = null, array $options = []): FormMapper
    {
        $this->adminFormMapper->add($name, $type, $options);

        return $this;
    }

    public function remove(string $key): FormMapper
    {
        $this->adminFormMapper->remove($key);

        return $this;
    }

    public function setHelps(array $helps = []): FormMapper
    {
        $this->adminFormMapper->setHelps($helps);

        return $this;
    }

    public function addHelp(string $name, string $help): FormMapper
    {
        $this->adminFormMapper->addHelp($name, $help);

        return $this;
    }

    public function has(string $key): bool
    {
        return $this->adminFormMapper->has($key);
    }

    public function get(string $name)
    {
        return $this->adminFormMapper->get($name);
    }
}
