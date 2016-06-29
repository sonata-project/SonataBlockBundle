UPGRADE 3.x
===========

## Deprecated AbstractBlockServiceTest class

The `Tests\Block\AbstractBlockServiceTest` class is deprecated. Use `Test\AbstractBlockServiceTestCase` instead.

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes. 
You can't extend them anymore, because they are only loaded when running internal tests. 
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).
