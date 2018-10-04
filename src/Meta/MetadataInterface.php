<?php

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
 *
 * NEXT_MAJOR: Remove CoreBundle dependency
 */
interface MetadataInterface extends \Sonata\CoreBundle\Model\MetadataInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return mixed
     */
    public function getImage();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param string $name    The option key
     * @param mixed  $default The default value if option not found
     *
     * @return mixed
     */
    public function getOption($name, $default = null);
}
