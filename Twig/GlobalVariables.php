<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GlobalVariables
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
{
    protected $container;

    protected $templates;

    /**
     *
     * @param ContainerInterface $container
     * @param array              $templates
     */
    public function __construct(ContainerInterface $container, array $templates)
    {
        $this->container = $container;
        $this->templates = $templates;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }
}
