<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Exception\Strategy;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for exception strategy management
 */
interface StrategyManagerInterface
{
    /**
     * Handles an exception for given block
     *
     * @param \Exception     $exception Exception to handle
     * @param BlockInterface $block     Block that provoked the exception
     * @param Response       $response  Response provided to the block service
     *
     * @return Response
     */
    function handleException(\Exception $exception, BlockInterface $block, Response $response = null);
}