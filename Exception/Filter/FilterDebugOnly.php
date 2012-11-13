<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Exception\Filter;

use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Exception\BlockExceptionInterface;

/**
 * Filter that handle exceptions only in debug mode
 */
class FilterDebugOnly implements FilterInterface
{
    /**
     * @var boolean
     */
    protected $debug;

    /**
     * Constructor
     *
     * @param boolean $debug
     */
    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $exception, BlockInterface $block)
    {
        return $this->debug ? true : false;
    }
}