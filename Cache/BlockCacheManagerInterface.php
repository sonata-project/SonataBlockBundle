<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Cache;

use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @author Serhii Polishchuk <spolischook@gmail.com>
 */
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
