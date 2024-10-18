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

use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcherComponentInterface;
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
 *     assets: array{
 *         js: string[],
 *         css: string[],
 *     }
 * }
 */
class BlockHelper
{
    /**
     * This property is a state variable holdings all assets used by the block for the current PHP request
     * It is used to correctly render the javascripts and stylesheets tags on the main layout.
     *
     * @var array{css: array<string>, js: array<string>}
     */
    private array $assets = ['css' => [], 'js' => []];

    /**
     * @var array<StopwatchEvent|array<string, mixed>>
     *
     * @phpstan-var array<StopwatchEvent|Trace>
     */
    private array $traces = [];

    /**
     * @var array<string, mixed>
     */
    private array $eventTraces = [];

    /**
     * @internal
     */
    public function __construct(
        private BlockRendererInterface $blockRenderer,
        private BlockContextManagerInterface $blockContextManager,
        private EventDispatcherInterface $eventDispatcher,
        private ?Stopwatch $stopwatch = null,
    ) {
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
            $html .= "\n".\sprintf('<script src="%s%s" type="text/javascript"></script>', $basePath, $javascript);
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

        $html = \sprintf("<style type='text/css' media='%s'>", $media);

        foreach ($this->assets['css'] as $stylesheet) {
            $html .= "\n".\sprintf('@import url(%s%s);', $basePath, $stylesheet);
        }

        $html .= "\n</style>";

        return $html;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function renderEvent(string $name, array $options = []): string
    {
        $eventName = \sprintf('sonata.block.event.%s', $name);

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
    public function render(string|array|BlockInterface $block, array $options = []): string
    {
        $blockContext = $this->blockContextManager->get($block, $options);

        $stats = null;

        if (null !== $this->stopwatch) {
            $stats = $this->startTracing($blockContext->getBlock());
        }

        $response = $this->blockRenderer->render($blockContext);

        if (null !== $stats) {
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
                \sprintf('The block %s has no stopwatch event to stop.', $block->getId() ?? '')
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
                $results[] = $listener[0]::class;
            } elseif (\is_array($listener) && \is_string($listener[0])) {
                $results[] = $listener[0];
            } else {
                $results[] = 'Unknown type!';
            }
        }

        return $results;
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
                \sprintf(
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
            'assets' => [
                'js' => [],
                'css' => [],
            ],
        ];
    }
}
