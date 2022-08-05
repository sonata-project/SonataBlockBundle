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

use Doctrine\Common\Collections\Collection;

/**
 * @method void addChild(BlockInterface $child)
 * @method bool hasChild()
 */
interface BlockInterface
{
    /**
     * @param string|int $id
     */
    public function setId($id): void;

    /**
     * @return string|int|null
     */
    public function getId();

    public function setName(string $name): void;

    public function getName(): ?string;

    public function setType(string $type): void;

    public function getType(): ?string;

    public function setEnabled(bool $enabled): void;

    public function getEnabled(): bool;

    public function setPosition(int $position): void;

    public function getPosition(): ?int;

    public function setCreatedAt(?\DateTime $createdAt = null): void;

    public function getCreatedAt(): ?\DateTime;

    public function setUpdatedAt(?\DateTime $updatedAt = null): void;

    public function getUpdatedAt(): ?\DateTime;

    /**
     * @deprecated since sonata-project/block-bundle 4.11 and will be removed in 5.0.
     */
    public function getTtl(): int;

    /**
     * @param array<string, mixed> $settings An array of key/value
     */
    public function setSettings(array $settings = []): void;

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array;

    /**
     * @param string $name  Key name
     * @param mixed  $value Value
     */
    public function setSetting(string $name, $value): void;

    /**
     * @param string     $name    Key name
     * @param mixed|null $default Default value
     *
     * @return mixed
     */
    public function getSetting(string $name, $default = null);

    /**
     * NEXT_MAJOR: Rename addChild().
     *
     * @deprecated since sonata-project/block-bundle 4.x. Use addChild() instead.
     */
    public function addChildren(self $children): void;

    /**
     * NEXT_MAJOR: Restrict typehint to Collection.
     *
     * @return Collection<int, BlockInterface>|array<BlockInterface> $children
     */
    public function getChildren();

    /**
     * NEXT_MAJOR: Rename hasChild().
     *
     * @deprecated since sonata-project/block-bundle 4.x. Use hasChild() instead.
     */
    public function hasChildren(): bool;

    public function setParent(?self $parent = null): void;

    public function getParent(): ?self;

    public function hasParent(): bool;
}
