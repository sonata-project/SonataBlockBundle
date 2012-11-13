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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * filter that ignores only exceptions inheriting a specific class
 */
class FilterIgnoreClass implements FilterInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor
     *
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(\Exception $exception, BlockInterface $block)
    {
        return ($exception instanceof $this->class);
    }
}