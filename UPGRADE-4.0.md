UPGRADE FROM 3.X to 4.0
=======================

## Removed CoreBundle

The SonataCoreBundle dependency was removed and replaced with split, smaller libraries.
- [sonata-project/form-extensions](https://github.com/sonata-project/form-extensions)
- [sonata-project/twig-extensions](https://github.com/sonata-project/twig-extensions)

You need to register the new extensions in you AppKernel.

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataAdminBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataAdminBundle/compare/3.x...4.0.0).

## Block id

If you have created a custom `AbstractBlockService` you must implement the new constructor, because all blocks use the service id as the block id. 

## Block constructor

Blocks are using `twig` service to render templates, so if you already overrode a constructor of a custom `AbstractBlockService`, you must update it.

Also, both arguments of `AbstractBlockService` constructor became required.
