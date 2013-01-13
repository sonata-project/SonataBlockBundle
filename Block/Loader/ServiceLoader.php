<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block\Loader;

use Sonata\BlockBundle\Block\BlockLoaderInterface;
use Sonata\BlockBundle\Model\Block;

class ServiceLoader implements BlockLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($configuration)
    {
        $block = new Block;
        $block->setId(uniqid());
        $block->setType($configuration['type']);
        $block->setEnabled(true);
        $block->setCreatedAt(new \DateTime);
        $block->setUpdatedAt(new \DateTime);

        // merge settings
        $block->setSettings(array_merge(
            isset($configuration['settings']) && is_array($configuration['settings']) ? $configuration['settings'] : array(),
            is_array($block->getSettings()) ? $block->getSettings() : array()
        ));

        return $block;
    }

    /**
     * {@inheritdoc}
     */
    public function support($configuration)
    {
        if (!is_array($configuration)) {
            return false;
        }

        if (!isset($configuration['type'])) {
            return false;
        }

        return true;
    }
}