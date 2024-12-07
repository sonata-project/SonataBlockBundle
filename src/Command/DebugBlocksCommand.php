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

namespace Sonata\BlockBundle\Command;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[AsCommand(name: 'debug:sonata:block', description: 'Debug all blocks available, show default settings of each block')]
final class DebugBlocksCommand extends Command
{
    public function __construct(private BlockServiceManagerInterface $blockManager)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addOption('context', 'c', InputOption::VALUE_REQUIRED, 'display service for the specified context');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = $input->getOption('context');
        if (\is_string($context)) {
            $services = $this->blockManager->getServicesByContext($context);
        } else {
            $services = $this->blockManager->getServices();
        }

        foreach ($services as $code => $service) {
            $output->writeln('');

            $title = '';
            if ($service instanceof EditableBlockService) {
                $title = \sprintf(' (<comment>%s</comment>)', $service->getMetadata()->getTitle());
            }
            $output->writeln(\sprintf('<info>>> %s</info>%s', $code, $title));

            $resolver = new OptionsResolver();
            $service->configureSettings($resolver);

            try {
                foreach ($resolver->resolve() as $key => $val) {
                    $output->writeln(\sprintf('    %-30s%s', $key, json_encode($val, \JSON_THROW_ON_ERROR)));
                }
            } catch (MissingOptionsException) {
                foreach ($resolver->getDefinedOptions() as $option) {
                    $output->writeln(\sprintf('    %s', $option));
                }
            }
        }

        $output->writeln('done!');

        return 0;
    }
}
