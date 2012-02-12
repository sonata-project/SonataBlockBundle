<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return \Sonata\BlockBundle\Block\BlockServiceManagerInterface
     */
    public function getBlockServiceManager()
    {
        return $this->getContainer()->get('sonata.block.manager');
    }
}