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

abstract class Block implements BlockInterface
{
    protected $settings;

    protected $enabled;

    protected $position;

    protected $parent;

    protected $children;

    protected $createdAt;

    protected $updatedAt;

    protected $type;

    protected $ttl;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function __construct()
    {
        $this->settings = array();
        $this->enabled  = false;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set settings
     *
     * @param array $settings
     */
    public function setSettings(array $settings = array())
    {
        $this->settings = $settings;
    }

    /**
     * Get settings
     *
     * @return array $settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
    }

    public function getSetting($name, $default = null)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return integer $position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add children
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface $child
     */
    public function addChildren(BlockInterface $child)
    {
        $this->children[] = $child;

        $child->setParent($this);
    }

    /**
     * Get children
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface $parent
     */
    public function setParent(BlockInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return \Sonata\BlockBundle\Model\BlockInterface $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function hasParent()
    {
        return $this->getParent() != null;
    }

    public function __toString()
    {
        return 'block (id:'.$this->getId().')';
    }

    /**
     * Returns the time to live of the block object
     *
     * @return integer
     */
    public function getTtl()
    {
        if ($this->ttl === null) {
            $ttl = $this->getSetting('ttl', 84600);

            foreach ($this->getChildren() as $block) {
                $blockTtl = $block->getTtl();

                $ttl = ($blockTtl < $ttl) ? $blockTtl : $ttl;
            }

            $this->ttl = $ttl;
        }

        return $this->ttl;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }
}