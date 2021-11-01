UPGRADE 4.x
===========

UPGRADE FROM 4.7 to 4.x
=======================

### `sonata-project/doctrine-extensions` is optional

By deprecating `Sonata\BlockBundle\Model\BlockManagerInterface`, the `sonata-project/doctrine-extensions` library is now optional.

UPGRADE FROM 4.6 to 4.7
=======================

### Sonata\BlockBundle\Form\Mapper\FormMapper

 - Removed the return type of `reorder`, `add`, `remove`.
 - Add the return type of `get`.
 - Add the param typehint of `add`.
 - Removed the method `setHelps` and `addHelp`.

Those changes are BC-break but
 - some of these are BC for PHP version >= 7.4.
 - others Sonata projects which used this interface didn't have already
the support of block-bundle 4.x.

So we'll assume the BC-break as acceptable and this will allow to provide
a compatibility between classes `Sonata\BlockBundle\Form\Mapper\FormMapper` 4.x
and `Sonata\AdminBundle\Form\Mapper\FormMapper` 4.x.

UPGRADE FROM 4.4 to 4.5
=======================

### Sonata\BlockBundle\Block\BlockContext

Passing a boolean to option "template" is deprecated and will not be allowed in 5.0, pass a `string` or `null` instead.
