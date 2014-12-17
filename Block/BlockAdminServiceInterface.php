<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Validator\ErrorElement;

interface BlockAdminServiceInterface
{
    /**
     * @param FormMapper     $form
     * @param BlockInterface $block
     *
     * @return void
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block);

    /**
     * @param FormMapper     $form
     * @param BlockInterface $block
     *
     * @return void
     */
    public function buildCreateForm(FormMapper $form, BlockInterface $block);

    /**
     * @param ErrorElement   $errorElement
     * @param BlockInterface $block
     *
     * @return void
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block);

    /**
     * @param null $code
     * 
     * @return MetadataInterface
     */
    public function getBlockMetadata($code = null);
}
