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
     * @param array<string, mixed> $settings An array of key/value
     */
    public function setSettings(array $settings = []): void;

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array;

    public function setSetting(string $name, mixed $value): void;

    public function getSetting(string $name, mixed $default = null): mixed;

    public function addChild(self $child): void;

    public function removeChild(self $child): void;

    /**
     * @return Collection<int, BlockInterface>
     */
    public function getChildren(): Collection;

    public function hasChild(): bool;

    public function setParent(?self $parent = null): void;

    public function getParent(): ?self;

    public function hasParent(): bool;
}
