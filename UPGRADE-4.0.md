UPGRADE FROM 3.X to 4.0
=======================

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](https://github.com/sonata-project/SonataAdminBundle/tree/3.x) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataAdminBundle/compare/3.x...4.0.0).

## Block id

If you have created a custom `AbstractBlockService` you must now implement the new constructor, because all blocks use the service id as the block id now. Because of that, the translation keys for the block ids have changed from `sonata.block.$NAME` to `sonata.block.service.$NAME`.
