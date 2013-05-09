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

    protected $bundleSettingsByType;

    protected $bundleSettingsByClass;

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
    public function addBundleSettingsByType($type, array $settings, $replace = false)
    {
        if ($replace) {
            $this->bundleSettingsByType[$type] = array_merge(isset($this->bundleSettingsByType[$type]) ? $this->bundleSettingsByType[$type] : array(), $settings);
        } else {
            $this->bundleSettingsByType[$type] = array_merge($settings, isset($this->bundleSettingsByType[$type]) ? $this->bundleSettingsByType[$type] : array());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addBundleSettingsByClass($class, array $settings, $replace = false)
    {
        if ($replace) {
            $this->bundleSettingsByClass[$class] = array_merge(isset($this->bundleSettingsByClass[$class]) ? $this->bundleSettingsByClass[$class] : array(), $settings);
        } else {
            $this->bundleSettingsByClass[$class] = array_merge($settings, isset($this->bundleSettingsByClass[$class]) ? $this->bundleSettingsByClass[$class] : array());
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

        // add bundle settings for block
        $class = $this->getClass($block);
        $bundleSettingsByType = isset($this->bundleSettingsByType[$block->getType()]) ? $this->bundleSettingsByType[$block->getType()] : array();
        $bundleSettingsByClass = isset($this->bundleSettingsByClass[$class]) ? $this->bundleSettingsByClass[$class] : array();
        $optionsResolver->setDefaults(array_merge($bundleSettingsByType, $bundleSettingsByClass));
    }
}
