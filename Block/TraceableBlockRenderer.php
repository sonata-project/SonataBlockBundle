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

/**
 * Renders a block using a block service and traces the block rendering
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class TraceableBlockRenderer extends BlockRenderer
{
    /**
     * @var array
     */
    protected $traces = array();

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
        if ($this->debug) {
            $this->startTracing($block);
        }

        $response = parent::render($block, $response);

        if ($this->debug) {
            $this->endTracing($block);
        }

        return $response;
    }

    /**
     * Start tracing the block rendering
     *
     * @param BlockInterface $block
     */
    public function startTracing(BlockInterface $block)
    {
        $this->traces[$block->getId()] = array(
            'name'          => $block->getName(),
            'type'          => $block->getType(),
            'time_start'    => microtime(true),
            'memory_start'  => memory_get_usage(true),
        );
    }

    /**
     * End tracing the block rendering
     *
     * @param BlockInterface $block
     */
    public function endTracing(BlockInterface $block)
    {
        $trace =& $this->traces[$block->getId()];

        $trace = array_merge($trace, array(
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