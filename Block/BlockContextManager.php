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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
     * @return BlockContextInterface
     *
     * @throws BlockOptionsException
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
     * @param OptionsResolverInterface $optionsResolver
     * @param BlockInterface           $block
     */
    protected function setDefaultSettings(OptionsResolverInterface $optionsResolver, BlockInterface $block)
    {
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
    }
}
