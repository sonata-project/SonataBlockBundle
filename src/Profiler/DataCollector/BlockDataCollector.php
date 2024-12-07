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
     * @param BlockHelper $blocksHelper   Block renderer
     * @param string[]    $containerTypes array of container types
     */
    public function __construct(
        private BlockHelper $blocksHelper,
        private array $containerTypes,
    ) {
        $this->reset();
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
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
     */
    public function getTotalBlock(): int
    {
        return \count($this->data['realBlocks']) + \count($this->data['containers']);
    }

    /**
     * Return the events used on the current page.
     *
     * @return array<string, mixed>
     */
    public function getEvents(): array
    {
        return $this->data['events'];
    }

    /**
     * Returns the block rendering history.
     *
     * @return array<string, mixed>
     */
    public function getBlocks(): array
    {
        return $this->data['blocks'];
    }

    /**
     * Returns the container blocks.
     *
     * @return array<string, mixed>
     */
    public function getContainers(): array
    {
        return $this->data['containers'];
    }

    /**
     * Returns the real blocks (non-container).
     *
     * @return array<string, mixed>
     */
    public function getRealBlocks(): array
    {
        return $this->data['realBlocks'];
    }

    public function getName(): string
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
