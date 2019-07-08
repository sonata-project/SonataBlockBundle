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

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @final since sonata-project/block-bundle 3.0
 */
class EmptyBlockService extends AbstractBlockService
{
    public function buildEditForm(FormMapper $form, BlockInterface $block): void
    {
        throw new \RuntimeException('Not used, this block renders an empty result if no block document can be found');
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return new Response();
    }
}
