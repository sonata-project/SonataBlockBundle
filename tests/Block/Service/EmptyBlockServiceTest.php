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

namespace Sonata\BlockBundle\Tests\Block\Service;

use Sonata\BlockBundle\Block\Service\EmptyBlockService;
use Sonata\BlockBundle\Test\BlockServiceTestCase;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class EmptyBlockServiceTest extends BlockServiceTestCase
{
    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testInvalidConstructor(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 passed to Sonata\BlockBundle\Block\Service\EmptyBlockService::__construct() must be a string or an instance of Twig\Environment or Symfony\Component\Templating\EngineInterface, instance of stdClass given.');

        new EmptyBlockService(new \stdClass());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testDeprecatedStringConstructor(): void
    {
        new EmptyBlockService('sonata.page.block.empty');
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     *
     * @expectedDeprecation The Sonata\BlockBundle\Test\FakeTemplating class is deprecated since 3.17 and will be removed in version 4.0.
     */
    public function testDeprecatedTemplatingConstructor(): void
    {
        new EmptyBlockService($this->templating);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     */
    public function testValiConstructor(): void
    {
        new EmptyBlockService($this->twig);
    }
}
