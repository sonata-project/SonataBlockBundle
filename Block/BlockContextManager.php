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

use Sonata\BlockBundle\Exception\BlockOptionsException;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlockContextManager implements BlockContextManagerInterface
{
    protected $blockLoader;

    protected $blockService;

    protected $settingsByType;

    protected $settingsByClass;

    /**
     * @param BlockLoaderInterface         $blockLoader
     * @param BlockServiceManagerInterface $blockService
     */
    public function __construct(BlockLoaderInterface $blockLoader, BlockServiceManagerInterface $blockService)
    {
        $this->blockLoader = $blockLoader;
        $this->blockService = $blockService;
    }

    /**
     * {@inheritdoc}
     */
    public function addSettingsByType($type, array $settings, $replace = false)
    {
        $typeSettings = isset($this->settingsByType[$type]) ? $this->settingsByType[$type] : array();
        if ($replace) {
            $this->settingsByType[$type] = array_merge($typeSettings, $settings);
        } else {
            $this->settingsByType[$type] = array_merge($settings, $typeSettings);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addSettingsByClass($class, array $settings, $replace = false)
    {
        $classSettings = isset($this->settingsByClass[$class]) ? $this->settingsByClass[$class] : array();
        if ($replace) {
            $this->settingsByClass[$class] = array_merge($classSettings, $settings);
        } else {
            $this->settingsByClass[$class] = array_merge($settings, $classSettings);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($meta, array $settings = array())
    {
        if (!$meta instanceof BlockInterface) {
            $block = $this->blockLoader->load($meta);

            if (is_array($meta) && isset($meta['settings'])) {
                // merge user settings
                $settings = array_merge($meta['settings'], $settings);
            }
        } else {
            $block = $meta;
        }

        if (!$block instanceof BlockInterface) {
            return false;
        }

        $optionsResolver = new OptionsResolver();

        $this->setDefaultSettings($optionsResolver, $block);

        $service = $this->blockService->get($block);
        $service->setDefaultSettings($optionsResolver, $block);

        try {
            $settings = $optionsResolver->resolve(array_merge($block->getSettings(), $settings));
        } catch (ExceptionInterface $e) {
            throw new BlockOptionsException($e);
        }

        return new BlockContext($block, $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(BlockInterface $block)
    {
        return get_class($block);
    }

    /**
     * @param OptionsResolverInterface $optionsResolver
     * @param BlockInterface           $block
     */
    protected function setDefaultSettings(OptionsResolverInterface $optionsResolver, BlockInterface $block)
    {
        // defaults for all blocks
        $optionsResolver->setDefaults(array(
            'use_cache'        => true,
            'extra_cache_keys' => array(),
            'attr'             => array(),
            'template'         => false,
            'ttl'              => (int)$block->getTtl(),
        ));

        $optionsResolver->addAllowedTypes(array(
            'use_cache'         => array('bool'),
            'extra_cache_keys'  => array('array'),
            'attr'              => array('array'),
            'ttl'               => array('int'),
            'template'          => array('string', 'bool'),
        ));

        // add type and class settings for block
        $class = $this->getClass($block);
        $settingsByType = isset($this->settingsByType[$block->getType()]) ? $this->settingsByType[$block->getType()] : array();
        $settingsByClass = isset($this->settingsByClass[$class]) ? $this->settingsByClass[$class] : array();
        $optionsResolver->setDefaults(array_merge($settingsByType, $settingsByClass));
    }
}