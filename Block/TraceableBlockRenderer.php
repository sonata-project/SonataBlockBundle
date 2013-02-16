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

    /**
     * @param BlockRendererInterface $blockRender The block renderer to trace
     */
    public function __construct(BlockRendererInterface $blockRender)
    {
        $this->blockRenderer = $blockRender;
    }

    /**
     * Renders a block and analyze metrics before and after the rendering
     *
     * @param BlockInterface $block    Block instance
     * @param Response       $response Response object
     *
     * @return Response
     */
    public function render(BlockInterface $block, Response $response = null)
    {
        $this->startTracing($block);

        $response = $this->blockRenderer->render($block, $response);

        $this->endTracing($block);

        return $response;
    }

    /**
     * Start tracing the block rendering
     *
     * @param BlockInterface $block
     */
    protected function startTracing(BlockInterface $block)
    {
        $this->traces[$block->getId()] = array(
            'name'         => $block->getName(),
            'type'         => $block->getType(),
            'time_start'    => microtime(true),
            'memory_start'  => memory_get_usage(true),
            'time_end'      => false,
            'memory_end'    => false,
            'memory_peak'   => false,
        );
    }

    /**
     * End tracing the block rendering
     *
     * @param BlockInterface $block
     */
    protected function endTracing(BlockInterface $block)
    {
        $this->traces[$block->getId()] = array_merge($this->traces[$block->getId()], array(
            'time_end'      => microtime(true),
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
