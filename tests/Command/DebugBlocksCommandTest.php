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

namespace Sonata\BlockBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Command\DebugBlocksCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class DebugBlocksCommandTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    protected function setUp(): void
    {
        $this->application = new Application();

        $blockManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockManager
            ->expects($this->any())
            ->method('getServices')
            ->willReturn([]);

        $this->application->add(new DebugBlocksCommand(null, $blockManager));
    }

    protected function tearDown(): void
    {
        $this->application = null;
    }

    public function testExecuteWithAlias(): void
    {
        $command = $this->application->find('debug:sonata:block');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'debug:sonata:block']);

        $this->assertSame("done!\n", $commandTester->getDisplay());
    }

    /**
     * @group legacy
     */
    public function testDebugBlocks(): void
    {
        $this->application = new Application();
        $twig = $this->createMock(Environment::class);

        $blockManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockManager
            ->expects($this->any())
            ->method('getServices')
            ->willReturn([
                'test.without_options' => new class('Test service block without options', $twig) extends AbstractBlockService {
                },
                'test.with_simple_option' => new class('Test service block with simple option', $twig) extends AbstractBlockService {
                    public function configureSettings(OptionsResolver $resolver): void
                    {
                        $resolver->setDefault('limit', 150);
                        $resolver->setAllowedTypes('limit', 'int');
                    }
                },
                'test.with_required_option' => new class('Test service block with required option', $twig) extends AbstractBlockService {
                    public function configureSettings(OptionsResolver $resolver): void
                    {
                        $resolver->setRequired('limit');
                        $resolver->setAllowedTypes('limit', 'int');
                    }
                },
            ]);

        $this->application->add(new DebugBlocksCommand(null, $blockManager));

        $command = $this->application->find('debug:sonata:block');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'debug:sonata:block']);

        $expected = <<<EOF

>> Test service block without options (test.without_options)

>> Test service block with simple option (test.with_simple_option)
    limit                         150

>> Test service block with required option (test.with_required_option)
    limit
done!

EOF;

        $this->assertSame($expected, $commandTester->getDisplay());
    }
}
