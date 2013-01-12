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

use Sonata\BlockBundle\Exception\BlockNotFoundException;
use Sonata\BlockBundle\Model\BlockInterface;

class BlockLoaderChain implements BlockLoaderInterface
{
    protected $loaders;
    protected $settings;

    /**
     * @param array $loaders
     * @param array $settings
     */
    public function __construct(array $loaders, array $settings = array())
    {
        $this->loaders = $loaders;

        foreach ($settings as $blockType => $blockSettings) {
            $this->setDefaultSettings($blockType, $blockSettings);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load($block)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->support($block)) {
                $block = $loader->load($block);

                if ($block instanceof BlockInterface) {
                    // merge settings
                    $block->setSettings(array_merge(
                        $this->getDefaultSettings($block->getType()),
                        is_array($block->getSettings()) ? $block->getSettings() : array()
                    ));
                }

                return $block;
            }
        }

        throw new BlockNotFoundException;
    }

    /**
     * {@inheritdoc}
     */
    public function support($name)
    {
        return true;
    }

    /**
     * @param string $blockType
     * @param array $defaultSettings
     */
    private function setDefaultSettings($blockType, array $defaultSettings)
    {
        $this->settings[$blockType] = $defaultSettings;
    }

    /**
     * @param string $blockType
     *
     * @return array
     */
    private function getDefaultSettings($blockType)
    {
        return isset($this->settings[$blockType]) ? $this->settings[$blockType] : array();
    }
}