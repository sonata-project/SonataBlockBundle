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
use Sonata\Form\Validator\ErrorElement;

/**
 * @author Christian Gripp <mail@core23.de>
 */
interface EditableBlockService
{
    public function configureEditForm(FormMapper $form, BlockInterface $block): void;

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void;

    public function validate(ErrorElement $errorElement, BlockInterface $block): void;

    public function getMetadata(): MetadataInterface;
}
