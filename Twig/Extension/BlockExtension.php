<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Twig\Extension;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockLoaderInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;

use Sonata\CacheBundle\Cache\CacheManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class BlockExtension extends \Twig_Extension
{
    private $blockServiceManager;

    private $cacheManager;

    private $environment;

    private $cacheBlocks;

    private $blockLoader;

    private $blockRenderer;

    /**
     * @param BlockServiceManagerInterface $blockServiceManager
     * @param CacheManagerInterface        $cacheManager
     * @param array                        $cacheBlocks
     * @param BlockLoaderInterface         $blockLoader
     * @param BlockRendererInterface       $blockRenderer
     */
    public function __construct(BlockServiceManagerInterface $blockServiceManager, array $cacheBlocks, BlockLoaderInterface $blockLoader, BlockRendererInterface $blockRenderer, CacheManagerInterface $cacheManager = null)
    {
        $this->blockServiceManager = $blockServiceManager;
        $this->cacheBlocks         = $cacheBlocks;
        $this->blockLoader         = $blockLoader;
        $this->blockRenderer       = $blockRenderer;
        $this->cacheManager        = $cacheManager;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'sonata_block_render'  => new \Twig_Function_Method($this, 'renderBlock', array('is_safe' => array('html'))),
            'sonata_block_include_javascripts'  => new \Twig_Function_Method($this, 'includeJavascripts', array('is_safe' => array('html'))),
            'sonata_block_include_stylesheets'  => new \Twig_Function_Method($this, 'includeStylesheets', array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sonata_block';
    }

    /**
     * @param $media screen|all ....
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
     * @throws \RuntimeException
     *
     * @param $block
     * @param bool  $useCache
     * @param array $extraCacheKeys
     *
     * @return string
     */
    public function renderBlock($block, $useCache = true, array $extraCacheKeys = array())
    {
        if (!$block instanceof BlockInterface) {
            $block = $this->blockLoader->load($block);

            // The loader match the block, but cannot find it
            if (!$block instanceof BlockInterface) {
                return '';
            }
        }

        $cacheKeys = false;
        $cacheService = $useCache ? $this->getCacheService($block) : false;
        if ($cacheService) {
            $cacheKeys = array_merge(
                $extraCacheKeys,
                $this->blockServiceManager->get($block)->getCacheKeys($block)
            );

            if ($cacheService->has($cacheKeys)) {
                $cacheElement = $cacheService->get($cacheKeys);
                if (!$cacheElement->isExpired() && $cacheElement->getData() instanceof Response) {
                    return $cacheElement->getData()->getContent();
                }
            }
        }

        $recorder = null;
        if ($this->cacheManager) {
            $recorder = $this->cacheManager->getRecorder();

            if ($recorder) {
                $recorder->add($block);
                $recorder->push();
            }
        }

        $response = $this->blockRenderer->render($block);
        $contextualKeys = $recorder ? $recorder->pop() : array();
        if ($response->isCacheable() && $cacheKeys && $cacheService) {
            $cacheService->set($cacheKeys, $response, $response->getTtl(), $contextualKeys);
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

        $type = isset($this->cacheBlocks[$block->getType()]) ? $this->cacheBlocks[$block->getType()] : false;

        if (!$type) {
            return false;
        }

        return $this->cacheManager->getCacheService($type);
    }
}
