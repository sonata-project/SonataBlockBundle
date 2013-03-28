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

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Sonata\BlockBundle\Exception\BlockOptionsException;

class BlockContextManager implements BlockContextManagerInterface
{
    protected $blockLoader;

    protected $blockService;

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
     * @param mixed $meta
     * @param array $settings
     *
     * @return BlockExecutionContextInterface
     *
     * @thrown BlockOptionsException
     */
    public function get($meta, array $settings = array())
    {
        if (!$meta instanceof BlockInterface) {
            $block = $this->blockLoader->load($meta);
        } else {
            $block = $meta;
        }

        if (!$block instanceof BlockInterface) {
            return false;
        }

        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults(array(
            'use_cache'        => true,
            'extra_cache_keys' => array(),
            'attr'             => array(),
            'template'         => false,
        ));

        $optionsResolver->addAllowedTypes(array(
            'use_cache'         => array('bool'),
            'extra_cache_keys'  => array('array'),
            'attr'              => array('array'),
            'template'          => array('string', 'bool'),
        ));

        $service = $this->blockService->get($block);
        $service->setDefaultSetttings($optionsResolver, $block);

        try {
            // inline options overwrite model one
            $settings = $optionsResolver->resolve(array_merge($block->getSettings(), $settings));
        } catch (ExceptionInterface $e) {
            throw new BlockOptionsException($e);
        }

        return new BlockExecutionContext($block, $settings);
    }
}