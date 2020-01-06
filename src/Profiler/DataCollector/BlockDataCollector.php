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

namespace Sonata\BlockBundle\Profiler\DataCollector;

use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Block data collector for the symfony web profiling.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class BlockDataCollector extends DataCollector
{
    /**
     * @var BlockHelper
     */
    private $blocksHelper;

    /**
     * @var array
     */
    private $containerTypes;

    /**
     * @param BlockHelper $blockHelper    Block renderer
     * @param array       $containerTypes array of container types
     */
    public function __construct(BlockHelper $blockHelper, array $containerTypes)
    {
        $this->blocksHelper = $blockHelper;
        $this->containerTypes = $containerTypes;
        $this->reset();
    }

    /**
     * TODO: add type declaration for exception when dropping php 7.3 or Symfony 4.
     */
    public function collect(Request $request, Response $response, $exception = null): void
    {
        $this->data['blocks'] = $this->blocksHelper->getTraces();

        // split into containers & real blocks
        foreach ($this->data['blocks'] as $id => $block) {
            if (!\is_array($block)) {
                return; // something went wrong while collecting information
            }

            if ('_events' === $id) {
                foreach ($block as $uniqid => $event) {
                    $this->data['events'][$uniqid] = $event;
                }

                continue;
            }

            if (\in_array($block['type'], $this->containerTypes, true)) {
                $this->data['containers'][$id] = $block;
            } else {
                $this->data['realBlocks'][$id] = $block;
            }
        }
    }

    /**
     * Returns the number of block used.
     *
     * @return int
     */
    public function getTotalBlock()
    {
        return \count($this->data['realBlocks']) + \count($this->data['containers']);
    }

    /**
     * Return the events used on the current page.
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->data['events'];
    }

    /**
     * Returns the block rendering history.
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->data['blocks'];
    }

    /**
     * Returns the container blocks.
     *
     * @return array
     */
    public function getContainers()
    {
        return $this->data['containers'];
    }

    /**
     * Returns the real blocks (non-container).
     *
     * @return array
     */
    public function getRealBlocks()
    {
        return $this->data['realBlocks'];
    }

    public function getName()
    {
        return 'block';
    }

    public function reset(): void
    {
        $this->data['blocks'] = [];
        $this->data['containers'] = [];
        $this->data['realBlocks'] = [];
        $this->data['events'] = [];
    }
}
