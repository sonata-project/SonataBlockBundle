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
 *     duration: int|float|false,
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
     * NEXT_MAJOR: remove.
     */
    private ?BlockServiceManagerInterface $blockServiceManager = null;

    /**
     * NEXT_MAJOR: remove this member and all related code to usages within this class.
     */
    private ?CacheManagerInterface $cacheManager = null;

    /**
     * NEXT_MAJOR: remove.
     *
     * @var array{by_class: array<class-string, string>, by_type: array<string, string>}
     */
    private array $cacheBlocks = ['by_class' => [], 'by_type' => []];

    private BlockRendererInterface $blockRenderer;

    private BlockContextManagerInterface $blockContextManager;

    private ?HttpCacheHandlerInterface $cacheHandler = null;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * This property is a state variable holdings all assets used by the block for the current PHP request
     * It is used to correctly render the javascripts and stylesheets tags on the main layout.
     *
     * @var array{css: array<string>, js: array<string>}
     */
    private array $assets = ['css' => [], 'js' => []];

    /**
     * @var array<StopwatchEvent|array<string, mixed>>
     * @phpstan-var array<StopwatchEvent|Trace>
     */
    private array $traces = [];

    /**
     * @var array<string, mixed>
     */
    private array $eventTraces = [];

    private ?Stopwatch $stopwatch = null;

    /**
     * NEXT_MAJOR: remove the deprecated signature and cleanup the constructor.
     *
     * @param array{by_class: array<class-string, string>, by_type: array<string, string>}|BlockContextManagerInterface|BlockRendererInterface $blockContextManagerOrBlockRendererOrCacheBlocks
     *
     * @internal
     */
    public function __construct(
        object $blockServiceManagerOrBlockRenderer,
        $blockContextManagerOrBlockRendererOrCacheBlocks,
        object $eventDispatcherOrBlockContextManagerOrBlockRenderer,
        ?object $stopWatchOrEventDispatcherOrBlockContextManager = null,
        ?object $stopwatchOrEventDispatcher = null,
        ?CacheManagerInterface $cacheManager = null,
        ?HttpCacheHandlerInterface $cacheHandler = null,
        ?Stopwatch $stopwatch = null
    ) {
        if ($blockServiceManagerOrBlockRenderer instanceof BlockServiceManagerInterface) {
            $this->blockServiceManager = $blockServiceManagerOrBlockRenderer;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 1 for method "%s" is deprecated since sonata-project/block-bundle 4.12. The argument will change to "%s" in 5.0.',
                    BlockServiceManagerInterface::class,
                    __METHOD__,
                    BlockRendererInterface::class
                ),
                \E_USER_DEPRECATED
            );
        } elseif ($blockServiceManagerOrBlockRenderer instanceof BlockRendererInterface) {
            $this->blockRenderer = $blockServiceManagerOrBlockRenderer;
        } else {
            throw new \TypeError(
                sprintf(
                    'Argument 1 of method "%s" must be an instance of "%s" or "%s"',
                    __METHOD__,
                    BlockRendererInterface::class,
                    BlockServiceManagerInterface::class
                )
            );
        }

        if ($blockContextManagerOrBlockRendererOrCacheBlocks instanceof BlockContextManagerInterface) {
            $this->blockContextManager = $blockContextManagerOrBlockRendererOrCacheBlocks;
        } elseif ($blockContextManagerOrBlockRendererOrCacheBlocks instanceof BlockRendererInterface) {
            $this->blockRenderer = $blockContextManagerOrBlockRendererOrCacheBlocks;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 2 for method "%s" is deprecated since sonata-project/block-bundle 4.12. The argument will change to "%s" in 5.0.',
                    BlockRendererInterface::class,
                    __METHOD__,
                    BlockContextManagerInterface::class
                ),
                \E_USER_DEPRECATED
            );
        } elseif (\is_array($blockContextManagerOrBlockRendererOrCacheBlocks)) {
            $this->cacheBlocks = $blockContextManagerOrBlockRendererOrCacheBlocks;
            @trigger_error(
                sprintf(
                    'Passing an array as argument 2 for method "%s" is deprecated since sonata-project/block-bundle 4.11. The argument will change to "%s" in 5.0.',
                    __METHOD__,
                    BlockContextManagerInterface::class
                ),
                \E_USER_DEPRECATED
            );
        } else {
            throw new \TypeError(
                sprintf(
                    'Argument 2 of method "%s" must be an array or an instance of "%s" or "%s"',
                    __METHOD__,
                    BlockRendererInterface::class,
                    BlockContextManagerInterface::class
                )
            );
        }

        if ($eventDispatcherOrBlockContextManagerOrBlockRenderer instanceof EventDispatcherInterface) {
            $this->eventDispatcher = $eventDispatcherOrBlockContextManagerOrBlockRenderer;
        } elseif ($eventDispatcherOrBlockContextManagerOrBlockRenderer instanceof BlockContextManagerInterface) {
            $this->blockContextManager = $eventDispatcherOrBlockContextManagerOrBlockRenderer;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 3 for method "%s" is deprecated since sonata-project/block-bundle 4.12. The argument will change to "%s" in 5.0.',
                    BlockContextManagerInterface::class,
                    __METHOD__,
                    EventDispatcherInterface::class
                ),
                \E_USER_DEPRECATED
            );
        } elseif ($eventDispatcherOrBlockContextManagerOrBlockRenderer instanceof BlockRendererInterface) {
            $this->blockRenderer = $eventDispatcherOrBlockContextManagerOrBlockRenderer;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 3 for method "%s" is deprecated since sonata-project/block-bundle 4.11. The argument will change to "%s" in 5.0.',
                    BlockRendererInterface::class,
                    __METHOD__,
                    EventDispatcherInterface::class
                ),
                \E_USER_DEPRECATED
            );
        } else {
            throw new \TypeError(
                sprintf(
                    'Argument 3 of method "%s" must be an instance of "%s" or "%s" or "%s"',
                    __METHOD__,
                    EventDispatcherInterface::class,
                    BlockContextManagerInterface::class,
                    BlockRendererInterface::class
                )
            );
        }

        if ($stopWatchOrEventDispatcherOrBlockContextManager instanceof Stopwatch || null === $stopWatchOrEventDispatcherOrBlockContextManager) {
            $this->stopwatch = $stopWatchOrEventDispatcherOrBlockContextManager;
        } elseif ($stopWatchOrEventDispatcherOrBlockContextManager instanceof EventDispatcherInterface) {
            $this->eventDispatcher = $stopWatchOrEventDispatcherOrBlockContextManager;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 4 for method "%s" is deprecated since sonata-project/block-bundle 4.12. The argument will change to "?%s" in 5.0.',
                    EventDispatcherInterface::class,
                    __METHOD__,
                    Stopwatch::class
                ),
                \E_USER_DEPRECATED
            );
        } elseif ($stopWatchOrEventDispatcherOrBlockContextManager instanceof BlockContextManagerInterface) {
            $this->blockContextManager = $stopWatchOrEventDispatcherOrBlockContextManager;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 4 for method "%s" is deprecated since sonata-project/block-bundle 4.11. The argument will change to "?%s" in 5.0.',
                    BlockContextManagerInterface::class,
                    __METHOD__,
                    Stopwatch::class
                ),
                \E_USER_DEPRECATED
            );
        } else {
            throw new \TypeError(
                sprintf(
                    'Argument 4 of method "%s" must be an instance of "%s" or "%s" or "%s" or null',
                    __METHOD__,
                    Stopwatch::class,
                    EventDispatcherInterface::class,
                    BlockContextManagerInterface::class
                )
            );
        }

        if ($stopwatchOrEventDispatcher instanceof Stopwatch) {
            $this->stopwatch = $stopwatchOrEventDispatcher;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 5 for method "%s" is deprecated since sonata-project/block-bundle 4.12. The argument will be removed in 5.0.',
                    Stopwatch::class,
                    __METHOD__,
                ),
                \E_USER_DEPRECATED
            );
        } elseif ($stopwatchOrEventDispatcher instanceof EventDispatcherInterface) {
            $this->eventDispatcher = $stopwatchOrEventDispatcher;
            $this->stopwatch = $stopwatch;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 5 for method "%s" is deprecated since sonata-project/block-bundle 4.11. The argument will be removed in 5.0.',
                    EventDispatcherInterface::class,
                    __METHOD__,
                ),
                \E_USER_DEPRECATED
            );
        } elseif (null !== $stopwatchOrEventDispatcher) {
            throw new \TypeError(
                sprintf(
                    'Argument 5 of method "%s" must be "null" or an instance of "%s" or "%s"',
                    __METHOD__,
                    Stopwatch::class,
                    EventDispatcherInterface::class
                )
            );
        }

        if (null !== $cacheManager) {
            $this->cacheManager = $cacheManager;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 6 for method "%s" is deprecated since sonata-project/block-bundle 4.11. The argument will be removed in 5.0.',
                    CacheAdapterInterface::class,
                    __METHOD__
                ),
                \E_USER_DEPRECATED
            );
        }

        if (null !== $cacheHandler) {
            $this->cacheHandler = $cacheHandler;
            @trigger_error(
                sprintf(
                    'Passing an instance of "%s" as argument 7 for method "%s" is deprecated since sonata-project/block-bundle 4.11. The argument will be removed in 5.0.',
                    HttpCacheHandlerInterface::class,
                    __METHOD__
                ),
                \E_USER_DEPRECATED
            );
        }
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

        // NEXT_MAJOR: simplify code and remove all cache-related usages
        $useCache = true === $blockContext->getSetting('use_cache');

        $cacheService = $useCache ? $this->getCacheService($blockContext->getBlock(), $stats) : null;
        if (null !== $cacheService) {
            if (null === $this->blockServiceManager) {
                throw new \LogicException(
                    sprintf(
                        'For caching functionality an instance of "%s" needs to be passed as first argument for "%s::__construct"',
                        BlockContextManagerInterface::class,
                        self::class
                    )
                );
            }
            $service = $this->blockServiceManager->get($blockContext->getBlock());
            $cacheKeys = array_merge(
                $service->getCacheKeys($blockContext->getBlock()),
                $blockContext->getSetting('extra_cache_keys')
            );

            if (null !== $this->stopwatch) {
                $stats['cache']['keys'] = $cacheKeys;
            }

            // Please note, some cache handler will always return true (js for instance)
            // This will allow to have a non cacheable block, but the global page can still be cached by
            // a reverse proxy, as the generated page will never get the generated Response from the block.
            if ($cacheService->has($cacheKeys)) {
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

        if (!isset($response)) {
            $recorder = null;
            if (null !== $this->cacheManager) {
                $recorder = $this->cacheManager->getRecorder();

                $recorder->add($blockContext->getBlock());
                $recorder->push();
            }

            $response = $this->blockRenderer->render($blockContext);
            $contextualKeys = null !== $recorder ? $recorder->pop() : [];

            if (null !== $this->stopwatch) {
                $stats['cache']['contextual_keys'] = $contextualKeys;
            }

            if ($response->isCacheable() && isset($cacheKeys) && null !== $cacheService) {
                $cacheService->set($cacheKeys, $response, (int) $response->getTtl(), $contextualKeys);
            }
        }

        if (null !== $this->stopwatch) {
            // avoid \DateTime because of serialize/unserialize issue in PHP7.3 (https://bugs.php.net/bug.php?id=77302)
            $responseDate = $response->getDate();
            $stats['cache']['created_at'] = null === $responseDate ? null : $responseDate->getTimestamp();
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
        $event = $this->traces[$block->getId() ?? ''];
        if (!$event instanceof StopwatchEvent) {
            throw new \InvalidArgumentException(
                sprintf('The block %s has no stopwatch event to stop.', $block->getId() ?? '')
            );
        }

        $event->stop();

        $this->traces[$block->getId() ?? ''] = [
            'duration' => $event->getDuration(),
            'memory_end' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
        ] + $stats;
    }

    /**
     * @return array<array{string|int, string}>
     */
    private function getEventBlocks(BlockEvent $event): array
    {
        $results = [];

        foreach ($event->getBlocks() as $block) {
            $results[] = [$block->getId() ?? '', $block->getType() ?? ''];
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
            } elseif (\is_array($listener) && \is_object($listener[0])) {
                $results[] = \get_class($listener[0]);
            } elseif (\is_array($listener) && \is_string($listener[0])) {
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
        if (null === $this->cacheManager) {
            return null;
        }

        // type by block class
        $class = ClassUtils::getClass($block);
        $cacheServiceId = $this->cacheBlocks['by_class'][$class] ?? null;

        // type by block service
        if (null === $cacheServiceId) {
            $cacheServiceId = $this->cacheBlocks['by_type'][$block->getType() ?? ''] ?? null;
        }

        if (null === $cacheServiceId) {
            return null;
        }

        if (null !== $this->stopwatch) {
            $stats['cache']['handler'] = $cacheServiceId;
        }

        return $this->cacheManager->getCacheService($cacheServiceId);
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return Trace
     */
    private function startTracing(BlockInterface $block): array
    {
        if (null !== $this->stopwatch) {
            $this->traces[$block->getId() ?? ''] = $this->stopwatch->start(
                sprintf(
                    '%s (id: %s, type: %s)',
                    $block->getName() ?? '',
                    $block->getId() ?? '',
                    $block->getType() ?? ''
                )
            );
        }

        return [
            'name' => $block->getName() ?? '',
            'type' => $block->getType() ?? '',
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
}
