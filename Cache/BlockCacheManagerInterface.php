<?php

namespace Sonata\BlockBundle\Cache;

use Sonata\BlockBundle\Model\BlockInterface;

interface BlockCacheManagerInterface
{
    /**
     * @param BlockInterface $block
     *
     * @return bool
     */
    public function setBlockToCache(BlockInterface $block);

    /**
     * @param string $blockName
     * @param array  $cacheKeys
     *
     * @return BlockInterface;
     */
    public function getBlockFromCache($blockName, array $cacheKeys);

    /**
     * @param BlockInterface $block
     *
     * @return \Sonata\Cache\CacheAdapterInterface;
     */
    public function getCacheService(BlockInterface $block);
}
