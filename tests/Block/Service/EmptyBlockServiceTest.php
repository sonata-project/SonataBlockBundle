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
     */
    public function testArgumentCheck()
    {
        new EmptyBlockService($this->twig);
        new EmptyBlockService('sonata.page.block.rss');

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 passed to Sonata\BlockBundle\Block\Service\EmptyBlockService::__construct() must be a string or an instance of Twig\Environment, instance of stdClass given.');
        new EmptyBlockService(new \stdClass());
    }
}
