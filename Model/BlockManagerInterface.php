<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Model;

interface BlockManagerInterface
{
    /**
     * Creates an empty block instance
     *
     * @return BlockInterface
     */
    public function create();

    /**
     * Deletes a block
     *
     * @param BlockInterface $block
     *
     * @return void
     */
    public function delete(BlockInterface $block);

    /**
     * Finds one block by the given criteria
     *
     * @param array $criteria
     *
     * @return BlockInterface
     */
    public function findOneBy(array $criteria);

    /**
     * Finds one block by the given criteria
     *
     * @param array $criteria
     *
     * @return BlockInterface
     */
    public function findBy(array $criteria);

    /**
     * Returns the block's fully qualified class name
     *
     * @return string
     */
    public function getClass();

    /**
     * Save a block
     *
     * @param BlockInterface $block
     *
     * @return void
     */
    public function save(BlockInterface $block);
}
