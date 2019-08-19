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
 * @final since sonata-project/block-bundle 3.0
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string|null
     */
    protected $image;

    /**
     * @var string|null
     */
    protected $domain;

    /**
     * @var array<string, mixed>
     */
    protected $options;

    /**
     * @param string               $title
     * @param string               $description
     * @param string|null          $image
     * @param string|null          $domain
     * @param array<string, mixed> $options
     */
    public function __construct($title, $description = null, $image = null, $domain = null, array $options = [])
    {
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->domain = $domain;
        $this->options = $options;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name, $default = null)
    {
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }
}
