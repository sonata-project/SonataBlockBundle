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

namespace Sonata\BlockBundle\Block;

use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;

interface BlockServiceManagerInterface
{
    /**
     * @param string $name
     * @param string $service
     * @param array  $contexts
     */
    public function add($name, $service, $contexts = []);

    /**
     * Return the block service linked to the link.
     *
     * @param BlockInterface $block
     *
     * @return BlockServiceInterface
     */
    public function get(BlockInterface $block);

    /**
     * @return array
     */
    public function getServices();

    /**
     * @param string $name
     * @param bool   $includeContainers
     *
     * @return array
     */
    public function getServicesByContext($name, $includeContainers = true);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * @param string $name
     *
     * @return BlockServiceInterface
     */
    public function getService($name);

    /**
     * @param ErrorElement   $errorElement
     * @param BlockInterface $block
     */
    public function validate(ErrorElement $errorElement, BlockInterface $block);
}
