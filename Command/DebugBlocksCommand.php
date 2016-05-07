<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebugBlocksCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:block:debug');
        $this->setDescription('Debug all blocks available, show default settings of each block');

        $this->addOption('context', 'c', InputOption::VALUE_REQUIRED, 'display service for the specified context');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('context')) {
            $services = $this->getBlockServiceManager()->getServicesByContext($input->getOption('context'));
        } else {
            $services = $this->getBlockServiceManager()->getServices();
        }

        foreach ($services as $code => $service) {
            $resolver = new OptionsResolver();
            $service->setDefaultSettings($resolver);

            $settings = $resolver->resolve();

            $output->writeln('');
            $output->writeln(sprintf('<info>>> %s</info> (<comment>%s</comment>)', $service->getName(), $code));

            foreach ($settings as $key => $val) {
                $output->writeln(sprintf('    %-30s%s', $key, json_encode($val)));
            }
        }

        $output->writeln('done!');
    }
}
