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

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class DebugBlocksCommandTest extends TestCase
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

    /**
     * @group legacy
     *
     * @expectedDeprecation Command "sonata:block:debug" is deprecated since sonata-project/block-bundle 3.x and will be removed with the 4.0 release. Use the "debug:sonata:block" command instead.
     */
    public function testExecute(): void
    {
        $command = $this->application->find('sonata:block:debug');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => 'sonata:block:debug']);

        $this->assertSame("done!\n", $commandTester->getDisplay());
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
     *
     * @expectedDeprecation Method Sonata\BlockBundle\Command\DebugBlocksCommand::getBlockServiceManager() is deprecated since sonata-project/block-bundle 3.x and will be removed with the 4.0 release.Use the Sonata\BlockBundle\Command\DebugBlocksCommand::$blockManager property instead.
     */
    public function testGetBlockServiceManager(): void
    {
        $blockManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockManager
            ->expects($this->any())
            ->method('getServices')
            ->willReturn([]);

        (new DebugBlocksCommand(null, $blockManager))->getBlockServiceManager();
    }

    public function testConstructorArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument 2 passed to Sonata\BlockBundle\Command\DebugBlocksCommand::__construct() must be an instance of Sonata\BlockBundle\Block\BlockServiceManagerInterface, NULL given.');

        new DebugBlocksCommand();
    }

    public function testDebugBlocks(): void
    {
        $this->application = new Application();

        $blockManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockManager
            ->expects($this->any())
            ->method('getServices')
            ->willReturn([
                'test.without_options' => new class extends AbstractBlockService {
                    public function getName()
                    {
                        return 'Test service block without options';
                    }
                },
                'test.with_simple_option' => new class extends AbstractBlockService {
                    public function configureSettings(OptionsResolver $resolver)
                    {
                        $resolver->setDefault('limit', 150);
                        $resolver->setAllowedTypes('limit', 'int');
                    }

                    public function getName()
                    {
                        return 'Test service block with simple option';
                    }
                },
                'test.with_required_option' => new class extends AbstractBlockService {
                    public function configureSettings(OptionsResolver $resolver)
                    {
                        $resolver->setRequired('limit');
                        $resolver->setAllowedTypes('limit', 'int');
                    }

                    public function getName()
                    {
                        return 'Test service block with required option';
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
