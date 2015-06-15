<?php

namespace Sonata\BlockBundle\Cache;

use Doctrine\Common\Util\ClassUtils;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Cache\CacheManagerInterface;

class BlockCacheManager implements BlockCacheManagerInterface
{
    /** @var  array */
    protected $cacheBlocks;

    /** @var  BlockContextManagerInterface */
    protected $blockContextManager;

    /** @var  CacheManagerInterface */
    protected $cacheManager;

    public function __construct(array $cacheBlocks, BlockContextManagerInterface $blockContextManager)
    {
        $this->cacheBlocks         = $cacheBlocks;
        $this->blockContextManager = $blockContextManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setBlockToCache(BlockInterface $block)
    {
        // TODO: Implement setBlockToCache() method.
    }

    /** {@inheritdoc} */
    public function getBlockFromCache($blockName, array $cacheKeys)
    {
        $blockContext = $this->blockContextManager->get(array('type' => $blockName));
        $block = $blockContext->getBlock();

        $cacheService = $this->getCacheService($block);

        if (false === $cacheService) {
            return false;
        }

        $cacheKeys = array_merge(
            $cacheKeys,
            $blockContext->getSetting('extra_cache_keys')
        );
        $cacheElement = $cacheService->get($cacheKeys);

        return $cacheElement->getData() ? $cacheElement->getData() : false;
    }

    /** {@inheritdoc} */
    public function getCacheService(BlockInterface $block)
    {
        if (!$this->cacheManager) {
            return false;
        }

        // type by block class
        $class = ClassUtils::getClass($block);
        $cacheServiceId = isset($this->cacheBlocks['by_class'][$class]) ? $this->cacheBlocks['by_class'][$class] : false;

        // type by block service
        if (!$cacheServiceId) {
            $cacheServiceId = isset($this->cacheBlocks['by_type'][$block->getType()]) ? $this->cacheBlocks['by_type'][$block->getType()] : false;
        }

        if (!$cacheServiceId) {
            return false;
        }

        return $this->cacheManager->getCacheService($cacheServiceId);
    }

    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
}
