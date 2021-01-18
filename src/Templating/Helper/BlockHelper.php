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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BlockHelper
{
    /**
     * @var BlockServiceManagerInterface
     */
    private $blockServiceManager;

    /**
     * @var CacheManagerInterface|null
     */
    private $cacheManager;

    /**
     * @var array
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
     * @var array
     */
    private $assets;

    /**
     * @var array
     */
    private $traces;

    /**
     * @var Stopwatch|null
     */
    private $stopwatch;

    public function __construct(
        BlockServiceManagerInterface $blockServiceManager,
        array $cacheBlocks,
        BlockRendererInterface $blockRenderer,
        BlockContextManagerInterface $blockContextManager,
        EventDispatcherInterface $eventDispatcher,
        ?CacheManagerInterface $cacheManager = null,
        ?HttpCacheHandlerInterface $cacheHandler = null,
        ?Stopwatch $stopwatch = null
    ) {
        $this->blockServiceManager = $blockServiceManager;
        $this->cacheBlocks = $cacheBlocks;
        $this->blockRenderer = $blockRenderer;
        $this->eventDispatcher = $eventDispatcher;
        $this->cacheManager = $cacheManager;
        $this->blockContextManager = $blockContextManager;
        $this->cacheHandler = $cacheHandler;
        $this->stopwatch = $stopwatch;

        $this->assets = [
            'js' => [],
            'css' => [],
        ];

        $this->traces = [
            '_events' => [],
        ];
    }

    /**
     * @param string $media    Unused, only kept to not break existing code
     * @param string $basePath Base path to prepend to the stylesheet urls
     *
     * @return array|string
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
     * @return array|string
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

    public function renderEvent(string $name, array $options = []): string
    {
        $eventName = sprintf('sonata.block.event.%s', $name);

        /**
         * @psalm-suppress TooManyArguments
         *
         * @todo remove annotation when Symfony 4.4.x support is dropped
         */
        $event = $this->eventDispatcher->dispatch(new BlockEvent($options), $eventName);

        \assert($event instanceof BlockEvent);

        $content = '';

        foreach ($event->getBlocks() as $block) {
            $content .= $this->render($block);
        }

        if (null !== $this->stopwatch) {
            $this->traces['_events'][uniqid('', true)] = [
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
     * @param mixed $block
     */
    public function render($block, array $options = []): string
    {
        $blockContext = $this->blockContextManager->get($block, $options);

        $stats = [];

        if ($this->stopwatch) {
            $stats = $this->startTracing($blockContext->getBlock());
        }

        $service = $this->blockServiceManager->get($blockContext->getBlock());

        $useCache = $blockContext->getSetting('use_cache');

        $cacheKeys = $response = false;
        $cacheService = $useCache ? $this->getCacheService($blockContext->getBlock(), $stats) : false;
        if ($cacheService) {
            $cacheKeys = array_merge(
                $service->getCacheKeys($blockContext->getBlock()),
                $blockContext->getSetting('extra_cache_keys')
            );

            if ($this->stopwatch) {
                $stats['cache']['keys'] = $cacheKeys;
            }

            // Please note, some cache handler will always return true (js for instance)
            // This will allows to have a non cacheable block, but the global page can still be cached by
            // a reverse proxy, as the generated page will never get the generated Response from the block.
            if ($cacheService->has($cacheKeys)) {
                $cacheElement = $cacheService->get($cacheKeys);

                if ($this->stopwatch) {
                    $stats['cache']['from_cache'] = false;
                }

                if (!$cacheElement->isExpired() && $cacheElement->getData() instanceof Response) {
                    /* @var Response $response */

                    if ($this->stopwatch) {
                        $stats['cache']['from_cache'] = true;
                    }

                    $response = $cacheElement->getData();
                }
            }
        }

        if (!$response) {
            $recorder = null;
            if ($this->cacheManager) {
                $recorder = $this->cacheManager->getRecorder();

                $recorder->add($blockContext->getBlock());
                $recorder->push();
            }

            $response = $this->blockRenderer->render($blockContext);
            $contextualKeys = $recorder ? $recorder->pop() : [];

            if ($this->stopwatch) {
                $stats['cache']['contextual_keys'] = $contextualKeys;
            }

            if ($response->isCacheable() && $cacheKeys && $cacheService) {
                $cacheService->set($cacheKeys, $response, (int) $response->getTtl(), $contextualKeys);
            }
        }

        if ($this->stopwatch) {
            // avoid \DateTime because of serialize/unserialize issue in PHP7.3 (https://bugs.php.net/bug.php?id=77302)
            $stats['cache']['created_at'] = null === $response->getDate() ? null : $response->getDate()->getTimestamp();
            $stats['cache']['ttl'] = $response->getTtl() ?: 0;
            $stats['cache']['age'] = $response->getAge();
        }

        // update final ttl for the whole Response
        if ($this->cacheHandler) {
            $this->cacheHandler->updateMetadata($response, $blockContext);
        }

        if ($this->stopwatch) {
            $this->stopTracing($blockContext->getBlock(), $stats);
        }

        return (string) $response->getContent();
    }

    /**
     * Returns the rendering traces.
     */
    public function getTraces(): array
    {
        return $this->traces;
    }

    private function stopTracing(BlockInterface $block, array $stats): void
    {
        $e = $this->traces[$block->getId()]->stop();

        $this->traces[$block->getId()] = array_merge($stats, [
            'duration' => $e->getDuration(),
            'memory_end' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
        ]);

        $this->traces[$block->getId()]['cache']['lifetime'] = $this->traces[$block->getId()]['cache']['age'] + $this->traces[$block->getId()]['cache']['ttl'];
    }

    private function getEventBlocks(BlockEvent $event): array
    {
        $results = [];

        foreach ($event->getBlocks() as $block) {
            $results[] = [$block->getId(), $block->getType()];
        }

        return $results;
    }

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

    private function getCacheService(BlockInterface $block, ?array &$stats = null): ?CacheAdapterInterface
    {
        if (!$this->cacheManager) {
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

        if ($this->stopwatch) {
            $stats['cache']['handler'] = $cacheServiceId;
        }

        return $this->cacheManager->getCacheService((string) $cacheServiceId);
    }

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
}
