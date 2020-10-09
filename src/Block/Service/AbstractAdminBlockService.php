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

use Sonata\AdminBundle\Form\FormMapper as AdminFormMapper;
use Sonata\BlockBundle\Form\Mapper\BlockFormMapper;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;

@trigger_error(
    'The '.__NAMESPACE__.'\AbstractAdminBlockService class is deprecated since sonata-project/block-bundle 3.16 '.
    'and will be removed with the 4.0 release.',
    E_USER_DEPRECATED
);

/**
 * @author Christian Gripp <mail@core23.de>
 *
 * @deprecated since sonata-project/block-bundle 3.16 without any replacement
 */
abstract class AbstractAdminBlockService extends AbstractBlockService implements AdminBlockServiceInterface
{
    /**
     * @deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.
     */
    public function prePersist(BlockInterface $block)
    {
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.
     */
    public function postPersist(BlockInterface $block)
    {
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.
     */
    public function preUpdate(BlockInterface $block)
    {
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.
     */
    public function postUpdate(BlockInterface $block)
    {
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.
     */
    public function preRemove(BlockInterface $block)
    {
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.12.0 and will be removed in version 4.0.
     */
    public function postRemove(BlockInterface $block)
    {
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.x. Use "configureCreateForm()" instead.
     */
    public function buildCreateForm(AdminFormMapper $formMapper, BlockInterface $block)
    {
        $blockFormMapper = new BlockFormMapper($formMapper);
        $this->configureCreateForm($blockFormMapper, $block);
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.x. Use "configureEditForm()" instead.
     */
    public function buildEditForm(AdminFormMapper $formMapper, BlockInterface $block)
    {
        $blockFormMapper = new BlockFormMapper($formMapper);
        $this->configureEditForm($blockFormMapper, $block);
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.x. Use "validate()" instead.
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        $this->validate($errorElement, $block);
    }

    /**
     * @deprecated since sonata-project/block-bundle 3.x. Use "getMetadata()" instead.
     */
    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', ['class' => 'fa fa-file']);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block)
    {
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block)
    {
        $this->configureEditForm($form, $block);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block)
    {
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata($this->getName(), $this->getName(), false, 'SonataBlockBundle', ['class' => 'fa fa-file']);
    }
}
