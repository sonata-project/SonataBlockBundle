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

use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Model\BlockInterface;

interface BlockServiceManagerInterface
{
    /**
     * @param string $name
     * @param string $service
     * @return void
     */
    function add($name, $service);

    /**
     * Return the block service linked to the link
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return \Sonata\BlockBundle\Block\BlockServiceInterface
     */
    function get(BlockInterface $block);

    /**
     * @param array $blockServices
     * @return void
     */
    function setServices(array $blockServices);

    /**
     * @return array
     */
    function getServices();

    /**
     *
     * @param string $name
     * @return boolean
     */
    function has($name);

    /**
     * @param $name
     * @return void
     */
    function getService($name);

    /**
     * @return array
     */
    function getLoadedServices();

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return void
     */
    function validate(ErrorElement $errorElement, BlockInterface $block);
}