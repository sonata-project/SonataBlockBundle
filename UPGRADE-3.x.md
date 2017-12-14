UPGRADE 3.x
===========

UPGRADE FROM 3.8 to 3.9
=======================

## Rewrote menu blocks

Defining menu blocks by using a `<tag name="sonata.block.menu"/>` tag was removed, because it never worked properly.

Use the existing `<tag name="knp_menu.menu" alias="app.main"/>` tag to define a menu block instead.

UPGRADE FROM 3.4 to 3.5
=======================

## Deprecated options resolver BC tricks.

Since we require at least Symfony 2.8, this BC trick is not needed anymore.

The concerned class and interface should be internal, but as they was not marked like this, we will deprecate them.

UPGRADE FROM 3.2 to 3.3
=======================

## Menu blocks

Menus for the `MenuBlockService` can now be defined by a `<tag name="sonata.block.menu"/>` tag. 
Defining the blocks via `sonata_block.menus` is deprecated.

## Inject blocks

Injecting the block id into a service is deprecated and will be automatically set.


Instead, provide an empty argument:

### Before
```xml
    <service id="acme.block.service" class="Acme\BlockBundle\AcmeBlockService">
        <tag name="sonata.block"/>
        <argument>acme.block.service</argument>
    </service>
```

### After
```xml
    <service id="acme.block.service" class="Acme\BlockBundle\AcmeBlockService">
        <tag name="sonata.block"/>
        <argument/>
    </service>
```

UPGRADE FROM 3.1 to 3.2
=======================

## Deprecated block classes and interfaces

The `Sonata\BlockBundle\Block\AbstractBlockService` and `Sonata\BlockBundle\Block\BaseBlockService` classes are deprecated.
Use `Sonata\BlockBundle\Block\Service\AbstractBlockService` for normal blocks
or `Sonata\BlockBundle\Block\Service\AbstractAdminBlockService` for manageable blocks instead.

The interfaces `Sonata\BlockBundle\Block\BlockServiceInterface` and `Sonata\BlockBundle\Block\BlockAdminServiceInterface` are deprecated.

UPGRADE FROM 3.0 to 3.1
=======================

## Deprecated test classes

The `Tests\Block\Service\FakeTemplating` class is deprecated. Use `Test\FakeTemplating` instead.
This is introduced on 3.1.1 because of a forgotten needed Merge Request.

## Deprecated AbstractBlockServiceTest class

The `Tests\Block\AbstractBlockServiceTest` class is deprecated. Use `Test\AbstractBlockServiceTestCase` instead.

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes.
You can't extend them anymore, because they are only loaded when running internal tests.
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).
