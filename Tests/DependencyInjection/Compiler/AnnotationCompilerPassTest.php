<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\DependencyInjection;

use JMS\DiExtraBundle\Metadata\ClassMetadata;
use Sonata\BlockBundle\Annotation\Block;

class AnnotationCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testMinimalBlock()
    {
        /*
         * @Block()
         */
        $annotation = new Block();

        $meta = new ClassMetadata('Sonata\BlockBundle\Tests\Fixtures\Block\FooBlock');

        $annotation->processMetadata($meta);

        $this->assertSame(
            $meta->tags['sonata.block'][0],
            array()
        );
    }

    public function testBlock()
    {
        /*
         * @Block()
         */
        $annotation = new Block();
        $annotation->id = 'block.test';

        $meta = new ClassMetadata('Sonata\BlockBundle\Tests\Fixtures\Block\FooBlock');

        $annotation->processMetadata($meta);

        $this->assertSame(
            $meta->tags['sonata.block'][0],
            array()
        );

        $this->assertSame(
            $meta->id,
            'block.test'
        );
    }
}
