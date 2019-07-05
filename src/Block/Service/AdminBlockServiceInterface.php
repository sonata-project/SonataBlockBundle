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
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Validator\ErrorElement;

/**
 * @author Christian Gripp <mail@core23.de>
 */
interface AdminBlockServiceInterface extends BlockServiceInterface
{
    /**
     * @param FormMapper     $form
     * @param BlockInterface $block
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block);

    /**
     * @param FormMapper     $form
     * @param BlockInterface $block
     */
    public function buildCreateForm(FormMapper $form, BlockInterface $block);

    /**
     * @param ErrorElement   $errorElement
     * @param BlockInterface $block
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block);

    /**
     * @param string|null $code
     *
     * @return MetadataInterface
     */
    public function getBlockMetadata($code = null);
}
