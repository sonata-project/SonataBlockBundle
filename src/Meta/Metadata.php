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
    private string $title;

    private ?string $description;

    private ?string $image;

    private ?string $domain;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $title,
        ?string $description = null,
        ?string $image = null,
        ?string $domain = null,
        array $options = []
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->domain = $domain;
        $this->options = $options;
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

    public function getOption(string $name, $default = null)
    {
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }
}
