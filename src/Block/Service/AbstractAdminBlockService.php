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

namespace Sonata\BlockBundle\Block\Service;

use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;

/**
 * @author Christian Gripp <mail@core23.de>
 */
abstract class AbstractAdminBlockService extends AbstractBlockService implements AdminBlockServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper, BlockInterface $block): void
    {
        $this->buildEditForm($formMapper, $block);
    }

    public function prePersist(BlockInterface $block): void
    {
    }

    public function postPersist(BlockInterface $block): void
    {
    }

    public function preUpdate(BlockInterface $block): void
    {
    }

    public function postUpdate(BlockInterface $block): void
    {
    }

    public function preRemove(BlockInterface $block): void
    {
    }

    public function postRemove(BlockInterface $block): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block): void
    {
    }

    public function validateBlock(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', ['class' => 'fa fa-file']);
    }
}
