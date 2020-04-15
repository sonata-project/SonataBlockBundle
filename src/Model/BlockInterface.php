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
     */
    public function setId($id): void;

    /**
     * Returns the block id.
     *
     * @return mixed void
     */
    public function getId();

    /**
     * Sets the name.
     */
    public function setName(string $name): void;

    /**
     * Returns the name.
     */
    public function getName(): ?string;

    /**
     * Sets the type.
     */
    public function setType(string $type): void;

    /**
     * Returns the type.
     */
    public function getType(): ?string;

    /**
     * Sets whether or not this block is enabled.
     */
    public function setEnabled(bool $enabled): void;

    /**
     * Returns whether or not this block is enabled.
     */
    public function getEnabled(): bool;

    /**
     * Set the block ordered position.
     */
    public function setPosition(int $position): void;

    /**
     * Returns the block ordered position.
     */
    public function getPosition(): ?int;

    /**
     * Sets the creation date and time.
     */
    public function setCreatedAt(?\DateTime $createdAt = null): void;

    /**
     * Returns the creation date and time.
     */
    public function getCreatedAt(): ?\DateTime;

    /**
     * Set the last update date and time.
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null): void;

    /**
     * Returns the last update date and time.
     */
    public function getUpdatedAt(): ?\DateTime;

    /**
     * Returns the block cache TTL.
     */
    public function getTtl(): int;

    /**
     * Sets the block settings.
     *
     * @param array $settings An array of key/value
     */
    public function setSettings(array $settings = []): void;

    /**
     * Returns the block settings.
     *
     * @return array $settings An array of key/value
     */
    public function getSettings(): array;

    /**
     * Sets one block setting.
     *
     * @param string $name  Key name
     * @param mixed  $value Value
     */
    public function setSetting(string $name, $value): void;

    /**
     * Returns one block setting or the given default value if no value is found.
     *
     * @param string     $name    Key name
     * @param mixed|null $default Default value
     *
     * @return mixed
     */
    public function getSetting(string $name, $default = null);

    /**
     * Add one child block.
     */
    public function addChildren(self $children): void;

    /**
     * Returns child blocks.
     *
     * @return BlockInterface[] $children
     */
    public function getChildren(): array;

    /**
     * Returns whether or not this block has children.
     */
    public function hasChildren(): bool;

    /**
     * Set the parent block.
     */
    public function setParent(?self $parent = null): void;

    /**
     * Returns the parent block.
     */
    public function getParent(): ?self;

    /**
     * Returns whether or not this block has a parent.
     */
    public function hasParent(): bool;
}
