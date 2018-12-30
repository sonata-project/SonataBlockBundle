<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BlockBundle\Tests\Meta;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Meta\Metadata;

class MetadataTest extends TestCase
{
    public function testGetters()
    {
        $metadata = new Metadata('title', 'description', 'image', 'domain', ['key1' => 'value1']);

        $this->assertSame('title', $metadata->getTitle());
        $this->assertSame('description', $metadata->getDescription());
        $this->assertSame('image', $metadata->getImage());
        $this->assertSame('domain', $metadata->getDomain());

        $this->assertSame('value1', $metadata->getOption('key1'));
        $this->assertSame('valueDefault', $metadata->getOption('none', 'valueDefault'));
        $this->assertNull($metadata->getOption('none'));
        $this->assertSame(['key1' => 'value1'], $metadata->getOptions());
        $this->assertSame('value1', $metadata->getOption('key1'));

        $metadata2 = new Metadata('title', 'description', 'image');
        $this->assertNull($metadata2->getDomain());
        $this->assertSame([], $metadata2->getOptions());
    }

    public function testImageNullGetDefaultImage()
    {
        $metadata = new Metadata('title', 'description');
        $this->assertSame($metadata->getImage(), $metadata::DEFAULT_MOSAIC_BACKGROUND);
    }

    public function testImageFalseDisableDefaultImage()
    {
        $metadata = new Metadata('title', 'description', false);
        $this->assertFalse($metadata->getImage());
    }

    /**
     * @dataProvider isImageAvailableProvider
     */
    public function testIsImageAvailable($title, $description, $image, $expected)
    {
        $metadata = new Metadata('title', 'description', $image);
        $this->assertEquals($expected, $metadata->isImageAvailable());
    }

    public function isImageAvailableProvider()
    {
        return [
            'image is null' => ['title', 'description', null, false],
            'image is false' => ['title', 'description', false, false],
            'image is available' => ['title', 'description', 'image.png', true]
        ];
    }
}
