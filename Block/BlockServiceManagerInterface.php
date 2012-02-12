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

interface BlockServiceManagerInterface
{
    /**
     * @param string $name
     * @param string $service
     * @return void
     */
    function addBlockService($name, $service);

    /**
     * Render a specialize block
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function renderBlock(BlockInterface $block);

    /**
     * Return the block service linked to the link
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return BlockServiceInterface
     */
    function getBlockService(BlockInterface $block);

    /**
     * @param array $blockServices
     * @return void
     */
    function setBlockServices(array $blockServices);

    /**
     * @return array
     */
    function getBlockServices();

    /**
     *
     * @param string $name
     * @return boolean
     */
    function hasBlockService($name);
}