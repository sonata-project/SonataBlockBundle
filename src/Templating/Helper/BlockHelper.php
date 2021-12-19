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

namespace Sonata\BlockBundle\Templating\Helper;

use Doctrine\Common\Util\ClassUtils;
use Psr\Cache\CacheItemPoolInterface;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Cache\HttpCacheHandlerInterface;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Cache\CacheAdapterInterface;
use Sonata\Cache\CacheManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcherComponentInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-type Trace = array{
 *     name: string,
 *     type: string,
 *     duration: int|false,
 *     memory_start: int|false,
 *     memory_end: int|false,
 *     memory_peak: int|false,
 *     cache: array{
 *         keys: mixed[],
 *         contextual_keys: mixed[],
 *         handler: false,
 *         from_cache: false,
 *         ttl: int,
 *         created_at: false,
 *         lifetime: int,
 *         age: int,
 *     },
 *     assets: array{
 *         js: string[],
 *         css: string[],
 *     }
 * }
 */
class BlockHelper
{
    /**
     * @var BlockServiceManagerInterface
     */
    private $blockServiceManager;

    /**
     * NEXT_MAJOR: Restrict to CacheItemPoolInterface|null.
     *
     * @var CacheManagerInterface|CacheItemPoolInterface|null
     */
    private $cacheItemPool;

    /**
     * @var array{by_class?: array<class-string, string>, by_type?: array<string, string>}
     */
    private $cacheBlocks;

    /**
     * @var BlockRendererInterface
     */
    private $blockRenderer;

    /**
     * @var BlockContextManagerInterface
     */
    private $blockContextManager;

    /**
     * @var HttpCacheHandlerInterface|null
     */
    private $cacheHandler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * This property is a state variable holdings all assets used by the block for the current PHP request
     * It is used to correctly render the javascripts and stylesheets tags on the main layout.
     *
     * @var array{css: array<string>, js: array<string>}
     */
    private $assets = ['css' => [], 'js' => []];

    /**
     * @var array<string, StopwatchEvent|array<string, mixed>>
     * @phpstan-var array<string, StopwatchEvent|Trace>
     */
    private $traces = [];

    /**
     * @var array<string, mixed>
     */
    private $eventTraces = [];

    /**
     * @var Stopwatch|null
     */
    private $stopwatch;

    /**
     * NEXT_MAJOR: Restrict $cacheItemPool to CacheItemPoolInterface|null and remove sonata/cache dependency.
     *
     * @param array{by_class?: array<class-string, string>, by_type?: array<string, string>} $cacheBlocks
     * @param CacheManagerInterface|CacheItemPoolInterface|null                              $cacheItemPool
     */
    public function __construct(
        BlockServiceManagerInterface $blockServiceManager,
        array $cacheBlocks,
        BlockRendererInterface $blockRenderer,
        BlockContextManagerInterface $blockContextManager,
        EventDispatcherInterface $eventDispatcher,
        $cacheItemPool = null,
        ?HttpCacheHandlerInterface $cacheHandler = null,
        ?Stopwatch $stopwatch = null
    ) {
        $this->blockServiceManager = $blockServiceManager;
        $this->cacheBlocks = $cacheBlocks;
        $this->blockRenderer = $blockRenderer;
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheItemPool = $cacheItemPool;
        $this->blockContextManager = $blockContextManager;
        $this->cacheHandler = $cacheHandler;
        $this->stopwatch = $stopwatch;
    }

    /**
     * @param string $media    Unused, only kept to not break existing code
     * @param string $basePath Base path to prepend to the stylesheet urls
     *
     * @return string
     */
    public function includeJavascripts($media, $basePath = '')
    {
        $html = '';
        foreach ($this->assets['js'] as $javascript) {
            $html .= "\n".sprintf('<script src="%s%s" type="text/javascript"></script>', $basePath, $javascript);
        }

        return $html;
    }

    /**
     * @param string $media    The css media type to use: all|screen|...
     * @param string $basePath Base path to prepend to the stylesheet urls
     *
     * @return string
     */
    public function includeStylesheets($media, $basePath = '')
    {
        if (0 === \count($this->assets['css'])) {
            return '';
        }

        $html = sprintf("<style type='text/css' media='%s'>", $media);

        foreach ($this->assets['css'] as $stylesheet) {
            $html .= "\n".sprintf('@import url(%s%s);', $basePath, $stylesheet);
        }

        $html .= "\n</style>";

        return $html;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function renderEvent(string $name, array $options = []): string
    {
        $eventName = sprintf('sonata.block.event.%s', $name);

        $event = $this->eventDispatcher->dispatch(new BlockEvent($options), $eventName);

        $content = '';

        foreach ($event->getBlocks() as $block) {
            $content .= $this->render($block);
        }

        if (null !== $this->stopwatch) {
            $this->eventTraces[uniqid('', true)] = [
                'template_code' => $name,
                'event_name' => $eventName,
                'blocks' => $this->getEventBlocks($event),
                'listeners' => $this->getEventListeners($eventName),
            ];
        }

        return $content;
    }

    /**
     * Check if a given block type exists.
     *
     * @param string $type Block type to check for
     */
    public function exists(string $type): bool
    {
        return $this->blockContextManager->exists($type);
    }

    /**
     * @param string|array<string, mixed>|BlockInterface $block
     * @param array<string, mixed>                       $options
     */
    public function render($block, array $options = []): string
    {
        $blockContext = $this->blockContextManager->get($block, $options);

        $stats = [];

        if (null !== $this->stopwatch) {
            $stats = $this->startTracing($blockContext->getBlock());
        }

        $service = $this->blockServiceManager->get($blockContext->getBlock());

        $useCache = true === $blockContext->getSetting('use_cache')
            && null !== $this->cacheItemPool;

        if ($useCache) {
            // NEXT_MAJOR: Keep only the if part.
            if ($this->cacheItemPool instanceof CacheItemPoolInterface) {
                $cacheKeys = $service->getCacheKeys($blockContext->getBlock());
                if (null !== $this->stopwatch) {
                    $stats['cache']['keys'] = $cacheKeys;
                }

                $cacheKey = $this->computeCacheKey($cacheKeys);
                if ($this->cacheItemPool->hasItem($cacheKey)) {
                    $cacheElement = $this->cacheItemPool->getItem($cacheKey);

                    if (null !== $this->stopwatch) {
                        $stats['cache']['from_cache'] = false;
                    }

                    if ($cacheElement->isHit() && $cacheElement->get() instanceof Response) {
                        if (null !== $this->stopwatch) {
                            $stats['cache']['from_cache'] = true;
                        }

                        $response = $cacheElement->get();
                    }
                }
            } else {
                $cacheKeys = array_merge(
                    $service->getCacheKeys($blockContext->getBlock()),
                    $blockContext->getSetting('extra_cache_keys')
                );
                if (null !== $this->stopwatch) {
                    $stats['cache']['keys'] = $cacheKeys;
                }

                $cacheService = $this->getCacheService($blockContext->getBlock(), $stats);

                // Please note, some cache handler will always return true (js for instance)
                // This will allow to have a non cacheable block, but the global page can still be cached by
                // a reverse proxy, as the generated page will never get the generated Response from the block.
                if (null !== $cacheService && $cacheService->has($cacheKeys)) {
                    $cacheElement = $cacheService->get($cacheKeys);

                    if (null !== $this->stopwatch) {
                        $stats['cache']['from_cache'] = false;
                    }

                    if (!$cacheElement->isExpired() && $cacheElement->getData() instanceof Response) {
                        if (null !== $this->stopwatch) {
                            $stats['cache']['from_cache'] = true;
                        }

                        $response = $cacheElement->getData();
                    }
                }
            }
        }

        if (!isset($response)) {
            $response = $this->blockRenderer->render($blockContext);

            if ($response->isCacheable() && $useCache) {
                // NEXT_MAJOR: Keep only the if part.
                if ($this->cacheItemPool instanceof CacheItemPoolInterface) {
                    $item = $this->cacheItemPool->getItem(
                        $this->computeCacheKey($service->getCacheKeys($blockContext->getBlock()))
                    );
                    $item->set($response);
                    $item->expiresAfter((int) $response->getTtl());
                    $this->cacheItemPool->save($item);
                } elseif (isset($cacheService)) {
                    $recorder = null;
                    if ($this->cacheItemPool instanceof CacheManagerInterface) {
                        $recorder = $this->cacheItemPool->getRecorder();

                        $recorder->add($blockContext->getBlock());
                        $recorder->push();
                    }

                    $contextualKeys = null !== $recorder ? $recorder->pop() : [];
                    if (null !== $this->stopwatch) {
                        $stats['cache']['contextual_keys'] = $contextualKeys;
                    }

                    $cacheService->set($cacheKeys, $response, (int) $response->getTtl(), $contextualKeys);
                }
            }
        }

        if (null !== $this->stopwatch) {
            // avoid \DateTime because of serialize/unserialize issue in PHP7.3 (https://bugs.php.net/bug.php?id=77302)
            $stats['cache']['created_at'] = null === $response->getDate() ? null : $response->getDate()->getTimestamp();
            $stats['cache']['ttl'] = $response->getTtl() ?? 0;
            $stats['cache']['age'] = $response->getAge();
            $stats['cache']['lifetime'] = $stats['cache']['age'] + $stats['cache']['ttl'];
        }

        // update final ttl for the whole Response
        if (null !== $this->cacheHandler) {
            $this->cacheHandler->updateMetadata($response, $blockContext);
        }

        if (null !== $this->stopwatch) {
            $this->stopTracing($blockContext->getBlock(), $stats);
        }

        return (string) $response->getContent();
    }

    /**
     * Returns the rendering traces.
     *
     * @return array<string, mixed>
     */
    public function getTraces(): array
    {
        return ['_events' => $this->eventTraces] + $this->traces;
    }

    /**
     * @param array<string, mixed> $stats
     *
     * @phpstan-param Trace $stats
     */
    private function stopTracing(BlockInterface $block, array $stats): void
    {
        $e = $this->traces[$block->getId()]->stop();

        $this->traces[$block->getId()] = array_merge($stats, [
            'duration' => $e->getDuration(),
            'memory_end' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
        ]);
    }

    /**
     * @return array<array{mixed, string}>
     */
    private function getEventBlocks(BlockEvent $event): array
    {
        $results = [];

        foreach ($event->getBlocks() as $block) {
            $results[] = [$block->getId(), $block->getType()];
        }

        return $results;
    }

    /**
     * @return string[]
     */
    private function getEventListeners(string $eventName): array
    {
        $results = [];

        if (!$this->eventDispatcher instanceof EventDispatcherComponentInterface) {
            return $results;
        }

        foreach ($this->eventDispatcher->getListeners($eventName) as $listener) {
            if ($listener instanceof \Closure) {
                $results[] = '{closure}()';
            } elseif (\is_object($listener[0])) {
                $results[] = \get_class($listener[0]);
            } elseif (\is_string($listener[0])) {
                $results[] = $listener[0];
            } else {
                $results[] = 'Unknown type!';
            }
        }

        return $results;
    }

    /**
     * @param array<string, mixed>|null $stats
     *
     * @phpstan-param Trace|null $stats
     */
    private function getCacheService(BlockInterface $block, ?array &$stats = null): ?CacheAdapterInterface
    {
        if (!$this->cacheItemPool instanceof CacheManagerInterface) {
            return null;
        }

        // type by block class
        $class = ClassUtils::getClass($block);
        $cacheServiceId = $this->cacheBlocks['by_class'][$class] ?? null;

        // type by block service
        if (null === $cacheServiceId) {
            $cacheServiceId = $this->cacheBlocks['by_type'][$block->getType()] ?? null;
        }

        if (null === $cacheServiceId) {
            return null;
        }

        if (null !== $this->stopwatch) {
            $stats['cache']['handler'] = $cacheServiceId;
        }

        return $this->cacheItemPool->getCacheService($cacheServiceId);
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return Trace
     */
    private function startTracing(BlockInterface $block): array
    {
        if (null !== $this->stopwatch) {
            $this->traces[$block->getId()] = $this->stopwatch->start(
                sprintf('%s (id: %s, type: %s)', $block->getName(), $block->getId(), $block->getType())
            );
        }

        return [
            'name' => $block->getName(),
            'type' => $block->getType(),
            'duration' => false,
            'memory_start' => memory_get_usage(true),
            'memory_end' => false,
            'memory_peak' => false,
            'cache' => [
                'keys' => [],
                'contextual_keys' => [],
                'handler' => false,
                'from_cache' => false,
                'ttl' => 0,
                'created_at' => false,
                'lifetime' => 0,
                'age' => 0,
            ],
            'assets' => [
                'js' => [],
                'css' => [],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $keys
     */
    private function computeCacheKey(array $keys): string
    {
        ksort($keys);

        return md5(serialize($keys));
    }
}
