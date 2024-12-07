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

namespace Sonata\BlockBundle\Meta;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
final class Metadata implements MetadataInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly string $title,
        private readonly ?string $description = null,
        private readonly ?string $image = null,
        private readonly ?string $domain = null,
        private array $options = [],
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }
}
