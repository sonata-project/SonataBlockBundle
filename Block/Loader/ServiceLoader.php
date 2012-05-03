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
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\BlockBundle\Model\Block;

class ServiceLoader implements BlockLoaderInterface
{
    protected $settings;

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings     = $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function load($configuration)
    {
        if (!$this->support($configuration)) {
            throw new \RuntimeException('Invalid block type, expected array');
        }

        $block = new Block;
        $block->setId(uniqid());
        $block->setSettings($this->getSettings($configuration));
        $block->setType($configuration['type']);
        $block->setEnabled(true);
        $block->setCreatedAt(new \DateTime);
        $block->setUpdatedAt(new \DateTime);

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

    /**
     * @throws \RuntimeException
     *
     * @param array $block
     *
     * @return array
     */
    private function getSettings($block)
    {
        if (!is_array($block) || !isset($block['type'])) {
            throw new \RuntimeException('Invalid block type, expected array');
        }

        if (!isset($this->settings[$block['type']])) {
            throw new \RuntimeException(sprintf('The block type %s does not exist', $block['type']));
        }

        return array_merge(
            $this->settings[$block['type']],
            isset($block['settings']) && is_array($block['settings']) ? $block['settings'] : array()
        );
    }
}