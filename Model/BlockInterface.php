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

/**
 * Interface of Block
 */
interface BlockInterface
{
    /**
     * Sets the block Id
     *
     * @param mixed $id
     *
     * @return void
     */
    function setId($id);

    /**
     * Returns the block id
     *
     * @return mixed void
     */
    function getId();

    /**
     * Sets the name
     *
     * @param string $name
     */
    function setName($name);

    /**
     * Returns the name
     *
     * @return string
     */
    function getName();

    /**
     * Sets the type
     *
     * @param string $type
     */
    function setType($type);

    /**
     * Returns the type
     *
     * @return string $type
     */
    function getType();

    /**
     * Sets whether or not this block is enabled
     *
     * @param boolean $enabled
     */
    function setEnabled($enabled);

    /**
     * Returns whether or not this block is enabled
     *
     * @return boolean $enabled
     */
    function getEnabled();

    /**
     * Set the block ordered position
     *
     * @param integer $position
     */
    function setPosition($position);

    /**
     * Returns the block ordered position
     *
     * @return integer $position
     */
    function getPosition();

    /**
     * Sets the creation date and time
     *
     * @param \Datetime $createdAt
     */
    function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Returns the creation date and time
     *
     * @return \Datetime $createdAt
     */
    function getCreatedAt();

    /**
     * Set the last update date and time
     *
     * @param \Datetime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Returns the last update date and time
     *
     * @return \Datetime $updatedAt
     */
    function getUpdatedAt();

    /**
     * Returns the block cache TTL
     *
     * @return integer
     */
    function getTtl();

    /**
     * Returns a string representation of the block
     *
     * @return string
     */
    function __toString();

    /**
     * Sets the block settings
     *
     * @param array $settings An array of key/value
     */
    function setSettings(array $settings = array());

    /**
     * Returns the block settings
     *
     * @return array $settings An array of key/value
     */
    function getSettings();

    /**
     * Sets one block setting
     *
     * @param string $name  Key name
     * @param mixed  $value Value
     */
    function setSetting($name, $value);

    /**
     * Returns one block setting or the given default value if no value is found
     *
     * @param string     $name    Key name
     * @param mixed|null $default Default value
     *
     * @return mixed
     */
    function getSetting($name, $default = null);

    /**
     * Add one child block
     *
     * @param BlockInterface $children
     */
    function addChildren(BlockInterface $children);

    /**
     * Returns child blocks
     *
     * @return \Doctrine\Common\Collections\Collection $children
     */
    function getChildren();

    /**
     * Returns whether or not this block has children
     *
     * @return boolean
     */
    function hasChildren();

    /**
     * Set the parent block
     *
     * @param BlockInterface|null $parent
     */
    function setParent(BlockInterface $parent = null);

    /**
     * Returns the parent block
     *
     * @return BlockInterface $parent
     */
    function getParent();

    /**
     * Returns whether or not this block has a parent
     *
     * @return void
     */
    function hasParent();
}