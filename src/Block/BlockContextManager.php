<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block;

use Doctrine\Common\Util\ClassUtils;
use Psr\Log\LoggerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlockContextManager implements BlockContextManagerInterface
{
    /**
     * @var BlockLoaderInterface
     */
    private $blockLoader;

    /**
     * @var BlockServiceManagerInterface
     */
    private $blockService;

    /**
     * @var array
     */
    private $settingsByType;

    /**
     * @var array
     */
    private $settingsByClass;

    /**
     * @var array
     */
    private $cacheBlocks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(BlockLoaderInterface $blockLoader, BlockServiceManagerInterface $blockService,
        array $cacheBlocks = [], LoggerInterface $logger = null
    ) {
        $this->blockLoader = $blockLoader;
        $this->blockService = $blockService;
        $this->cacheBlocks = $cacheBlocks;
        $this->logger = $logger;
    }

    public function addSettingsByType(string $type, array $settings, bool $replace = false): void
    {
        $typeSettings = isset($this->settingsByType[$type]) ? $this->settingsByType[$type] : [];
        if ($replace) {
            $this->settingsByType[$type] = array_merge($typeSettings, $settings);
        } else {
            $this->settingsByType[$type] = array_merge($settings, $typeSettings);
        }
    }

    public function addSettingsByClass(string $class, array $settings, bool $replace = false): void
    {
        $classSettings = isset($this->settingsByClass[$class]) ? $this->settingsByClass[$class] : [];
        if ($replace) {
            $this->settingsByClass[$class] = array_merge($classSettings, $settings);
        } else {
            $this->settingsByClass[$class] = array_merge($settings, $classSettings);
        }
    }

    /**
     * Check if a given block type exists.
     *
     * @param string $type Block type to check for
     */
    public function exists(string $type): bool
    {
        return $this->blockLoader->exists($type);
    }

    /**
     * {@inheritdoc}
     */
    public function get($meta, array $settings = [])
    {
        if (!$meta instanceof BlockInterface) {
            $block = $this->blockLoader->load($meta);

            if (\is_array($meta) && isset($meta['settings'])) {
                // merge user settings
                $settings = array_merge($meta['settings'], $settings);
            }
        } else {
            $block = $meta;
        }

        if (!$block instanceof BlockInterface) {
            return false;
        }

        $originalSettings = $settings;

        try {
            $settings = $this->resolve($block, array_merge($block->getSettings(), $settings));
        } catch (ExceptionInterface $e) {
            if ($this->logger) {
                $this->logger->error(sprintf(
                    '[cms::blockContext] block.id=%s - error while resolving options - %s',
                    $block->getId(),
                    $e->getMessage()
                ));
            }

            $settings = $this->resolve($block, $settings);
        }

        $blockContext = new BlockContext($block, $settings);

        $this->setDefaultExtraCacheKeys($blockContext, $originalSettings);

        return $blockContext;
    }

    private function configureSettings(OptionsResolver $optionsResolver, BlockInterface $block): void
    {
        // defaults for all blocks
        $optionsResolver->setDefaults([
            'use_cache' => true,
            'extra_cache_keys' => [],
            'attr' => [],
            'template' => false,
            'ttl' => (int) $block->getTtl(),
        ]);

        $optionsResolver
                ->addAllowedTypes('use_cache', 'bool')
                ->addAllowedTypes('extra_cache_keys', 'array')
                ->addAllowedTypes('attr', 'array')
                ->addAllowedTypes('ttl', 'int')
                ->addAllowedTypes('template', ['string', 'bool'])
            ;

        // add type and class settings for block
        $class = ClassUtils::getClass($block);
        $settingsByType = isset($this->settingsByType[$block->getType()]) ? $this->settingsByType[$block->getType()] : [];
        $settingsByClass = isset($this->settingsByClass[$class]) ? $this->settingsByClass[$class] : [];
        $optionsResolver->setDefaults(array_merge($settingsByType, $settingsByClass));
    }

    /**
     * Adds context settings, to be able to rebuild a block context, to the
     * extra_cache_keys.
     */
    private function setDefaultExtraCacheKeys(BlockContextInterface $blockContext, array $settings): void
    {
        if (!$blockContext->getSetting('use_cache') || $blockContext->getSetting('ttl') <= 0) {
            return;
        }

        $block = $blockContext->getBlock();

        // type by block class
        $class = ClassUtils::getClass($block);
        $cacheServiceId = isset($this->cacheBlocks['by_class'][$class]) ? $this->cacheBlocks['by_class'][$class] : false;

        // type by block service
        if (!$cacheServiceId) {
            $cacheServiceId = isset($this->cacheBlocks['by_type'][$block->getType()]) ? $this->cacheBlocks['by_type'][$block->getType()] : false;
        }

        if (!$cacheServiceId) {
            // no context cache needed
            return;
        }

        // do not add cache settings to extra_cache_keys
        unset($settings['use_cache'], $settings['extra_cache_keys'], $settings['ttl']);

        $extraCacheKeys = $blockContext->getSetting('extra_cache_keys');

        // add context settings to extra_cache_keys
        if (!isset($extraCacheKeys[self::CACHE_KEY])) {
            $extraCacheKeys[self::CACHE_KEY] = $settings;
            $blockContext->setSetting('extra_cache_keys', $extraCacheKeys);
        }
    }

    private function resolve(BlockInterface $block, array $settings): array
    {
        $optionsResolver = new OptionsResolver();

        $this->configureSettings($optionsResolver, $block);

        $service = $this->blockService->get($block);
        $service->configureSettings($optionsResolver);

        return $optionsResolver->resolve($settings);
    }
}
