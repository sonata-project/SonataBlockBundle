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

use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class CustomBlockServiceTest extends BlockServiceTestCase
{
    use ExpectDeprecationTrait;

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testArgumentDeprecation()
    {
        $this->expectDeprecation('Passing string as argument 1 to Sonata\BlockBundle\Block\Service\AbstractBlockService@anonymous::__construct() is deprecated since sonata-project/block-bundle 3.16 and will throw a \TypeError as of 4.0. You must pass an instance of Twig\Environment instead.');

        new class('block') extends AbstractBlockService {
        };
    }
}
