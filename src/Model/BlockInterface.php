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

namespace Sonata\BlockBundle\Model;

/**
 * Interface of Block.
 */
interface BlockInterface
{
    /**
     * Sets the block Id.
     *
     * @param mixed $id
     *
     * @deprecated since sonata-project/block-bundle 3.x, to be removed in 4.0.
     *             You should implement the id inside your bundle.
     */
    public function setId($id);

    /**
     * Returns the block id.
     *
     * @return mixed void
     *
     * @deprecated since sonata-project/block-bundle 3.x, to be removed in 4.0.
     *             You should implement the id inside your bundle.
     */
    public function getId();

    /**
     * Sets the name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Returns the name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Sets the type.
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Returns the type.
     *
     * @return string|null $type
     */
    public function getType();

    /**
     * Sets whether or not this block is enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * Returns whether or not this block is enabled.
     *
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * Set the block ordered position.
     *
     * @param int $position
     */
    public function setPosition($position);

    /**
     * Returns the block ordered position.
     *
     * @return int|null $position
     */
    public function getPosition();

    /**
     * Sets the creation date and time.
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Returns the creation date and time.
     *
     * @return \DateTime|null $createdAt
     */
    public function getCreatedAt();

    /**
     * Set the last update date and time.
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Returns the last update date and time.
     *
     * @return \DateTime|null $updatedAt
     */
    public function getUpdatedAt();

    /**
     * Returns the block cache TTL.
     *
     * @return int
     */
    public function getTtl();

    /**
     * Sets the block settings.
     *
     * @param array $settings An array of key/value
     */
    public function setSettings(array $settings = []);

    /**
     * Returns the block settings.
     *
     * @return array $settings An array of key/value
     */
    public function getSettings();

    /**
     * Sets one block setting.
     *
     * @param string $name  Key name
     * @param mixed  $value Value
     */
    public function setSetting($name, $value);

    /**
     * Returns one block setting or the given default value if no value is found.
     *
     * @param string     $name    Key name
     * @param mixed|null $default Default value
     *
     * @return mixed
     */
    public function getSetting($name, $default = null);

    /**
     * Add one child block.
     *
     * @param BlockInterface $children
     */
    public function addChildren(self $children);

    /**
     * Returns child blocks.
     *
     * @return BlockInterface[] $children
     */
    public function getChildren();

    /**
     * Returns whether or not this block has children.
     *
     * @return bool
     */
    public function hasChildren();

    /**
     * Set the parent block.
     *
     * @param BlockInterface|null $parent
     */
    public function setParent(self $parent = null);

    /**
     * Returns the parent block.
     *
     * @return BlockInterface|null $parent
     */
    public function getParent();

    /**
     * Returns whether or not this block has a parent.
     *
     * @return bool
     */
    public function hasParent();
}
