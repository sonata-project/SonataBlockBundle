<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\BlockBundle\Profiler\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sonata\BlockBundle\Block\TraceableBlockRenderer;

/**
 * Block data collector for the symfony web profiling
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class BlockDataCollector implements DataCollectorInterface, \Serializable
{
    /**
     * @var TraceableBlockRenderer
     */
    protected $renderer;

    /**
     * @var array
     */
    protected $blocks = array();

    /**
     * @var array
     */
    protected $containers = array();

    /**
     * @var array
     */
    protected $realBlocks = array();

    protected $containerTypes = array();

    /**
     * Constructor
     *
     * @param TraceableBlockRenderer $renderer       Block renderer
     * @param array                  $containerTypes array of container types
     */
    public function __construct(TraceableBlockRenderer $renderer, array $containerTypes)
    {
        $this->renderer = $renderer;
        $this->containerTypes = $containerTypes;
    }

    /**
     * Collects the traces from the block renderer
     *
     * @param Request    $request   Http Request
     * @param Response   $response  Http Response
     * @param \Exception $exception Exception thrown
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->blocks = $this->renderer->getTraces();

        // split into containers & real blocks
        foreach ($this->blocks as $block) {
            if (in_array($block['type'], $this->containerTypes)) {
                $this->containers[] = $block;
            } else {
                $this->realBlocks[] = $block;
            }
        }
    }

    /**
     * Returns the block rendering history
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Returns the container blocks
     *
     * @return array
     */
    public function getContainers()
    {
        return $this->containers;
    }

    /**
     * Returns the real blocks (non-container)
     *
     * @return array
     */
    public function getRealBlocks()
    {
        return $this->realBlocks;
    }

    /**
     * serialize the data
     *
     * @return string
     */
    public function serialize()
    {
        $data = array(
            'blocks'     => $this->blocks,
            'containers' => $this->containers,
            'realBlocks' => $this->realBlocks,
        );

        return serialize($data);
    }

    /**
     * Unserialize the data
     *
     * @param string $data
     *
     * @return void
     */
    public function unserialize($data)
    {
        $merged = unserialize($data);

        $this->blocks     = $merged['blocks'];
        $this->containers = $merged['containers'];
        $this->realBlocks = $merged['realBlocks'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'block';
    }
}
