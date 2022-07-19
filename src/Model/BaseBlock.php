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
 * Base abstract Block class that provides a default implementation of the block interface.
 */
abstract class BaseBlock implements BlockInterface
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var array<string, mixed>
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var int|null
     */
    protected $position;

    /**
     * @var BlockInterface|null
     */
    protected $parent;

    /**
     * NEXT_MAJOR: Restrict typehint to Collection.
     *
     * @var Collection<int, BlockInterface>|array<BlockInterface>
     */
    protected $children;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     */
    protected $updatedAt;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * NEXT_MAJOR: remove.
     *
     * @deprecated since sonata-project/block-bundle 4.12 and will be removed in 5.0
     *
     * @var int|null
     */
    protected $ttl;

    public function __construct()
    {
        $this->settings = [];
        $this->enabled = false;
        $this->children = [];
    }

    /**
     * NEXT_MAJOR: Add return typehint.
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function __toString()
    {
        return sprintf('%s ~ #%s', $this->getName() ?? '', $this->getId() ?? '');
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setSettings(array $settings = []): void
    {
        $this->settings = $settings;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function setSetting(string $name, $value): void
    {
        $this->settings[$name] = $value;
    }

    public function getSetting(string $name, $default = null)
    {
        return $this->settings[$name] ?? $default;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setCreatedAt(?\DateTime $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function addChild(BlockInterface $child): void
    {
        $this->children[] = $child;

        $child->setParent($this);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function addChildren(BlockInterface $children): void
    {
        @trigger_error(
            sprintf(
                'Method "%s" is deprecated since sonata-project/block-bundle 4.x. Use "addChild" instead.',
                __METHOD__
            ),
            \E_USER_DEPRECATED
        );

        $this->children[] = $children;

        $children->setParent($this);
    }

    public function getChildren(): iterable
    {
        return $this->children;
    }

    public function setParent(?BlockInterface $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParent(): ?BlockInterface
    {
        return $this->parent;
    }

    public function hasParent(): bool
    {
        return $this->getParent() instanceof self;
    }

    /**
     * @deprecated since sonata-project/block-bundle 4.11 and will be removed in 5.0.
     */
    public function getTtl(): int
    {
        if (false === $this->getSetting('use_cache', true)) {
            return 0;
        }

        $ttl = $this->getSetting('ttl', 86400);

        foreach ($this->getChildren() as $block) {
            $blockTtl = $block->getTtl();

            $ttl = ($blockTtl < $ttl) ? $blockTtl : $ttl;
        }

        $this->ttl = $ttl;

        return $this->ttl;
    }

    public function hasChild(): bool
    {
        return \count($this->children) > 0;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function hasChildren(): bool
    {
        @trigger_error(
            sprintf(
                'Method "%s" is deprecated since sonata-project/block-bundle 4.x. Use "hasChild" instead.',
                __METHOD__
            ),
            \E_USER_DEPRECATED
        );

        return \count($this->children) > 0;
    }
}
