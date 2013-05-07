<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Block;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Renders a block using a block service and traces the block rendering
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TraceableBlockRenderer implements BlockRendererInterface
{
    /**
     * @var array
     */
    protected $traces = array();

    protected $blockRenderer;

    protected $stopwatch;

    /**
     * @param BlockRendererInterface $blockRender The block renderer to trace
     * @param Stopwatch              $stopwatch   A Stopwatch instance
     */
    public function __construct(BlockRendererInterface $blockRender, Stopwatch $stopwatch = null)
    {
        $this->blockRenderer = $blockRender;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function render(BlockContextInterface $blockContext, Response $response = null)
    {
        if ($this->stopwatch instanceof Stopwatch) {
            $e = $this->startTracing($blockContext->getBlock());

            $response = $this->blockRenderer->render($blockContext, $response);

            $this->endTracing($blockContext->getBlock(), $e);
        } else {
            $response = $this->blockRenderer->render($blockContext, $response);
        }

        return $response;
    }

    /**
     * Start tracing the block rendering
     *
     * @param BlockInterface $block
     *
     * @return StopWatchEvent
     */
    protected function startTracing(BlockInterface $block)
    {
        $this->traces[$block->getId()] = array(
            'name'          => $block->getName(),
            'type'          => $block->getType(),
            'duration'      => false,
            'memory_start'  => memory_get_usage(true),
            'memory_end'    => false,
            'memory_peak'   => false,
        );

        $name = sprintf('%s (id: %s, type: %s)', $block->getName(), $block->getId(), $block->getType());

        return $this->stopwatch->start($name);
    }

    /**
     * End tracing the block rendering
     *
     * @param BlockInterface $block
     * @param StopWatchEvent $e
     */
    protected function endTracing(BlockInterface $block, StopwatchEvent $e)
    {
        $e->stop();

        $this->traces[$block->getId()] = array_merge($this->traces[$block->getId()], array(
            'duration'      => $e->getDuration(),
            'memory_end'    => memory_get_usage(true),
            'memory_peak'   => memory_get_peak_usage(true),
        ));
    }

    /**
     * Returns the rendering traces
     *
     * @return array
     */
    public function getTraces()
    {
        return $this->traces;
    }
}
