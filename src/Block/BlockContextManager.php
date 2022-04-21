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
use Psr\Log\NullLogger;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Options;
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
     * @var array<string, array<string, mixed>>
     */
    private $settingsByType = [];

    /**
     * @var array<string, array<string, mixed>>
     * @phpstan-var array<class-string, array<string, mixed>>
     */
    private $settingsByClass = [];

    /**
     * NEXT_MAJOR: remove.
     *
     * @var array{by_class: array<class-string, string>, by_type: array<string, string>}
     */
    private $cacheBlocks = ['by_class' => [], 'by_type' => []];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NEXT_MAJOR: remove $cacheBlocksOrLogger argument.
     *
     * @param array{by_class: array<class-string, string>, by_type: array<string, string>}|LoggerInterface|null $cacheBlocksOrLogger
     */
    public function __construct(
        BlockLoaderInterface $blockLoader,
        BlockServiceManagerInterface $blockService,
        $cacheBlocksOrLogger = null,
        ?LoggerInterface $logger = null
    ) {
        $this->blockLoader = $blockLoader;
        $this->blockService = $blockService;

        // NEXT_MAJOR: remove if/else block completely and uncomment following line
        // $this->logger = $logger ?? new NullLogger();
        if (\is_array($cacheBlocksOrLogger)) {
            $this->cacheBlocks = $cacheBlocksOrLogger;
            @trigger_error(
                sprintf(
                    'Passing an array as argument 3 for method "%s" is deprecated since sonata-project/block-bundle 4.x. The argument will change to "?%s" in 5.0.',
                    __METHOD__,
                    LoggerInterface::class
                ),
                \E_USER_DEPRECATED
            );
            $this->logger = new NullLogger();
        } elseif ($cacheBlocksOrLogger instanceof LoggerInterface) {
            $this->logger = $cacheBlocksOrLogger;
        } elseif (null === $cacheBlocksOrLogger) {
            $this->logger = new NullLogger();
        } else {
            throw new \TypeError('Argument 3 must be null|array|LoggerInterface');
        }

        // NEXT_MAJOR: remove
        if (null !== $logger) {
            $this->logger = $logger;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 4 to method "%s" is deprecated since sonata-project/block-bundle 4.x. The argument will be removed in 5.0.',
                    LoggerInterface::class,
                    __METHOD__
                ),
                \E_USER_DEPRECATED
            );
        }
    }

    public function addSettingsByType(string $type, array $settings, bool $replace = false): void
    {
        $typeSettings = $this->settingsByType[$type] ?? [];
        if ($replace) {
            $this->settingsByType[$type] = array_merge($typeSettings, $settings);
        } else {
            $this->settingsByType[$type] = array_merge($settings, $typeSettings);
        }
    }

    public function addSettingsByClass(string $class, array $settings, bool $replace = false): void
    {
        $classSettings = $this->settingsByClass[$class] ?? [];
        if ($replace) {
            $this->settingsByClass[$class] = array_merge($classSettings, $settings);
        } else {
            $this->settingsByClass[$class] = array_merge($settings, $classSettings);
        }
    }

    public function exists(string $type): bool
    {
        return $this->blockLoader->exists($type);
    }

    public function get($meta, array $settings = []): BlockContextInterface
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

        $originalSettings = $settings;

        try {
            $settings = $this->resolve($block, array_merge($block->getSettings(), $settings));
        } catch (ExceptionInterface $e) {
            $this->logger->error(sprintf(
                '[cms::blockContext] block.id=%s - error while resolving options - %s',
                $block->getId() ?? '',
                $e->getMessage()
            ));

            // NEXT_MAJOR: Only pass the template value if it's a string.
            $settings = $this->resolve($block, $settings + ['template' => $block->getSetting('template')]);
        }

        $blockContext = new BlockContext($block, $settings);

        // NEXT_MAJOR: remove next line
        $this->setDefaultExtraCacheKeys($blockContext, $originalSettings);

        return $blockContext;
    }

    private function configureSettings(OptionsResolver $optionsResolver, BlockInterface $block): void
    {
        // defaults for all blocks
        $optionsResolver->setDefaults([
            // NEXT_MAJOR: remove
            'use_cache' => true,
            // NEXT_MAJOR: remove
            'extra_cache_keys' => [],
            'attr' => [],
            'template' => null, // NEXT_MAJOR: Remove the default value
            // NEXT_MAJOR: remove
            'ttl' => $block->getTtl(),
        ]);

        $optionsResolver
            // NEXT_MAJOR: remove
            ->addAllowedTypes('use_cache', 'bool')
            // NEXT_MAJOR: remove
            ->addAllowedTypes('extra_cache_keys', 'array')
            ->addAllowedTypes('attr', 'array')
            // NEXT_MAJOR: remove
            ->addAllowedTypes('ttl', 'int')
            // NEXT_MAJOR: Remove bool and null.
            ->addAllowedTypes('template', ['null', 'string', 'bool'])
            // NEXT_MAJOR: Remove setDeprecated.
            ->setDeprecated(
                'template',
                ...$this->deprecationParameters(
                    '4.5.0',
                    static function (Options $options, $value): string {
                        if (\is_bool($value)) {
                            return 'Not passing a string value to option "template" is deprecated and will not be allowed in 5.0.';
                        }

                        return '';
                    }
                )
            )
            // NEXT_MAJOR: Remove setDeprecated.
            ->setDeprecated(
                'use_cache',
                ...$this->deprecationParameters(
                    '4.11',
                    'Block option "use_cache" is deprecated since sonata-project/block-bundle 4.11 and will be removed in 5.0.'
                )
            )
            // NEXT_MAJOR: Remove setDeprecated.
            ->setDeprecated(
                'extra_cache_keys',
                ...$this->deprecationParameters(
                    '4.11',
                    'Block option "extra_cache_keys" is deprecated since sonata-project/block-bundle 4.11 and will be removed in 5.0.'
                )
            )
            // NEXT_MAJOR: Remove setDeprecated.
            ->setDeprecated(
                'ttl',
                ...$this->deprecationParameters(
                    '4.11',
                    'Block option "ttl" is deprecated since sonata-project/block-bundle 4.11 and will be removed in 5.0.'
                )
            );

        // add type and class settings for block
        $class = ClassUtils::getClass($block);
        $settingsByType = $this->settingsByType[$block->getType() ?? ''] ?? [];
        $settingsByClass = $this->settingsByClass[$class] ?? [];
        $optionsResolver->setDefaults(array_merge($settingsByType, $settingsByClass));
    }

    /**
     * // NEXT_MAJOR: remove this method.
     *
     * Adds context settings, to be able to rebuild a block context, to the
     * extra_cache_keys.
     *
     * @param array<string, mixed> $settings
     */
    private function setDefaultExtraCacheKeys(BlockContextInterface $blockContext, array $settings): void
    {
        if (false === $blockContext->getSetting('use_cache') || $blockContext->getSetting('ttl') <= 0) {
            return;
        }

        $block = $blockContext->getBlock();

        // type by block class
        $class = ClassUtils::getClass($block);
        $cacheServiceId = $this->cacheBlocks['by_class'][$class] ?? null;

        // type by block service
        if (null === $cacheServiceId) {
            $cacheServiceId = $this->cacheBlocks['by_type'][$block->getType() ?? ''] ?? null;
        }

        if (null === $cacheServiceId) {
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

    /**
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    private function resolve(BlockInterface $block, array $settings): array
    {
        $optionsResolver = new OptionsResolver();

        $this->configureSettings($optionsResolver, $block);

        $service = $this->blockService->get($block);
        $service->configureSettings($optionsResolver);

        return $optionsResolver->resolve($settings);
    }

    /**
     * This class is a BC layer for deprecation messages for symfony/options-resolver < 5.1.
     * Remove this class when dropping support for symfony/options-resolver < 5.1.
     *
     * @param string|\Closure $message
     *
     * @return mixed[]
     */
    private function deprecationParameters(string $version, $message): array
    {
        // @phpstan-ignore-next-line
        if (method_exists(OptionsResolver::class, 'define')) {
            return [
                'sonata-project/block-bundle',
                $version,
                $message,
            ];
        }

        return [$message];
    }
}
