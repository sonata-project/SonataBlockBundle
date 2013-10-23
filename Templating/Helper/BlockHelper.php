<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Templating\Helper;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Cache\HttpCacheHandlerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;

use Sonata\CacheBundle\Cache\CacheManagerInterface;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Util\ClassUtils;

class BlockHelper extends Helper
{
    private $blockServiceManager;

    private $cacheManager;

    private $cacheBlocks;

    private $blockRenderer;

    private $blockContextManager;

    private $cacheHandler;

    /**
     * @param BlockServiceManagerInterface $blockServiceManager
     * @param array                        $cacheBlocks
     * @param BlockRendererInterface       $blockRenderer
     * @param BlockContextManagerInterface $blockContextManager
     * @param CacheManagerInterface        $cacheManager
     * @param HttpCacheHandlerInterface    $cacheHandler
     */
    public function __construct(BlockServiceManagerInterface $blockServiceManager, array $cacheBlocks, BlockRendererInterface $blockRenderer, BlockContextManagerInterface $blockContextManager, CacheManagerInterface $cacheManager = null, HttpCacheHandlerInterface $cacheHandler = null)
    {
        $this->blockServiceManager = $blockServiceManager;
        $this->cacheBlocks         = $cacheBlocks;
        $this->blockRenderer       = $blockRenderer;
        $this->cacheManager        = $cacheManager;
        $this->blockContextManager = $blockContextManager;
        $this->cacheHandler        = $cacheHandler;
    }

    public function getName()
    {
        return 'sonata_block';
    }

    /**
     * @param $media screen|all ....
     *
     * @return array|string
     */
    public function includeJavascripts($media)
    {
        $javascripts = array();

        foreach ($this->blockServiceManager->getLoadedServices() as $service) {
            $javascripts = array_merge($javascripts, $service->getJavascripts($media));
        }

        if (count($javascripts) == 0) {
            return '';
        }

        $html = "";
        foreach ($javascripts as $javascript) {
            $html .= "\n" . sprintf('<script src="%s" type="text/javascript"></script>', $javascript);
        }

        return $html;
    }

    /**
     * @param $media
     *
     * @return array|string
     */
    public function includeStylesheets($media)
    {
        $stylesheets = array();

        foreach ($this->blockServiceManager->getLoadedServices() as $service) {
            $stylesheets = array_merge($stylesheets, $service->getStylesheets($media));
        }

        if (count($stylesheets) == 0) {
            return '';
        }

        $html = sprintf("<style type='text/css' media='%s'>", $media);

        foreach ($stylesheets as $stylesheet) {
            $html .= "\n" . sprintf('@import url(%s);', $stylesheet, $media);
        }

        $html .= "\n</style>";

        return $html;
    }

    /**
     * @param mixed $block
     * @param array $options
     *
     * @return null|Response
     */
    public function render($block, array $options = array())
    {
        $blockContext = $this->blockContextManager->get($block, $options);

        if (!$blockContext instanceof BlockContextInterface) {
            return '';
        }

        $useCache = $blockContext->getSetting('use_cache');

        $cacheKeys = $response = false;
        $cacheService = $useCache ? $this->getCacheService($blockContext->getBlock()) : false;
        if ($cacheService) {
            $cacheKeys = array_merge(
                $this->blockServiceManager->get($blockContext->getBlock())->getCacheKeys($blockContext->getBlock()),
                $blockContext->getSetting('extra_cache_keys')
            );

            // Please note, some cache handler will always return true (js for instance)
            // This will allows to have a non cacheable block, but the global page can still be cached by
            // a reverse proxy, as the generated page will never get the generated Response from the block.
            if ($cacheService->has($cacheKeys)) {
                $cacheElement = $cacheService->get($cacheKeys);

                if (!$cacheElement->isExpired() && $cacheElement->getData() instanceof Response) {
                    $response = $cacheElement->getData();
                }
            }
        }

        if (!$response) {
            $recorder = null;
            if ($this->cacheManager) {
                $recorder = $this->cacheManager->getRecorder();

                if ($recorder) {
                    $recorder->add($blockContext->getBlock());
                    $recorder->push();
                }
            }

            $response = $this->blockRenderer->render($blockContext);
            $contextualKeys = $recorder ? $recorder->pop() : array();

            if ($response->isCacheable() && $cacheKeys && $cacheService) {
                $cacheService->set($cacheKeys, $response, $response->getTtl(), $contextualKeys);
            }
        }

        // update final ttl for the whole Response
        if ($this->cacheHandler) {
            $this->cacheHandler->updateMetadata($response, $blockContext);
        }

        return $response->getContent();
    }

    /**
     * @param BlockInterface $block
     *
     * @return \Sonata\CacheBundle\Cache\CacheInterface;
     */
    protected function getCacheService(BlockInterface $block)
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
}
