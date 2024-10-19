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
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlockContextManager implements BlockContextManagerInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $settingsByType = [];

    /**
     * @var array<string, array<string, mixed>>
     *
     * @phpstan-var array<class-string, array<string, mixed>>
     */
    private array $settingsByClass = [];

    private LoggerInterface $logger;

    public function __construct(
        private BlockLoaderInterface $blockLoader,
        private BlockServiceManagerInterface $blockService,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
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

            $block->setSettings($settings);
        } else {
            $block = $meta;
        }

        try {
            $settings = $this->resolve($block, array_merge($block->getSettings(), $settings));
        } catch (ExceptionInterface $e) {
            $this->logger->error(\sprintf(
                '[cms::blockContext] block.id=%s - error while resolving options - %s',
                $block->getId() ?? '',
                $e->getMessage()
            ));

            $template = $block->getSetting('template');

            $settings = $this->resolve(
                $block,
                $settings + (\is_string($template) ? ['template' => $template] : [])
            );
        }

        return new BlockContext($block, $settings);
    }

    private function configureSettings(OptionsResolver $optionsResolver, BlockInterface $block): void
    {
        // defaults for all blocks
        $optionsResolver->setDefaults([
            'attr' => [],
        ]);

        $optionsResolver->setDefined('template');

        $optionsResolver
            ->addAllowedTypes('attr', 'array')
            ->addAllowedTypes('template', 'string');

        // add type and class settings for block
        $class = ClassUtils::getClass($block);
        $settingsByType = $this->settingsByType[$block->getType() ?? ''] ?? [];
        $settingsByClass = $this->settingsByClass[$class] ?? [];
        $optionsResolver->setDefaults(array_merge($settingsByType, $settingsByClass));
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
}
