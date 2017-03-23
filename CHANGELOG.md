# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.3.2](https://github.com/sonata-project/SonataBlockBundle/compare/3.3.1...3.3.2) - 2017-03-23
### Fixed
- Resolve container parameters before comparing class names
- Internal deprecations finally fixed

## [3.3.1](https://github.com/sonata-project/SonataBlockBundle/compare/3.3.0...3.3.1) - 2017-02-28
### Fixed
- Profiler block Twig 2.0 compatibility
- Some unwanted deprecation notices about code we can't change until next major version have been removed

## [3.3.0](https://github.com/sonata-project/SonataBlockBundle/compare/3.2.0...3.3.0) - 2017-01-17
### Added
- Created `MenuManager` to collect all menus for the `MenuBlockService`
- Added new `sonata.block_menu` tag

### Changed
- Empty block names are automatically set via `TweakCompilerPass`

### Deprecated
- Deprecated the array parameter in `MenuBlockService`in favor of the new `MenuManager`

### Fixed
- Missing italian translation

### Removed
- Deprecated `BaseBlockService` class was removed from the list of classes to compile

## [3.2.0](https://github.com/sonata-project/SonataBlockBundle/compare/3.1.1...3.2.0) - 2016-09-20
### Added
- Created `Sonata\BlockBundle\Block\Service\AbstractAdminBlockService` class
- Created `Sonata\BlockBundle\Block\Service\AbstractBlockService` class
- Created `Sonata\BlockBundle\Block\Service\AdminBlockServiceInterface` class
- Created `Sonata\BlockBundle\Block\Service\BlockServiceInterface` class

### Deprecated
- The class `Sonata\BlockBundle\Block\AbstractBlockService` is deprecated
- The class `Sonata\BlockBundle\Block\BaseBlockService` is deprecated
- The class `Sonata\BlockBundle\Block\BlockAdminServiceInterface` is deprecated
- The class `Sonata\BlockBundle\Block\BlockServiceInterface` is deprecated

## [3.1.1](https://github.com/sonata-project/SonataBlockBundle/compare/3.1.0...3.1.1) - 2016-07-12
### Deprecated
- Deprecate `Tests\Block\Service\FakeTemplating` in favor of `Test\Mock\MockTemplating` (missing PR for 3.1.0)

## [3.1.0](https://github.com/sonata-project/SonataBlockBundle/compare/3.0.1...3.1.0) - 2016-07-12
### Changed
- Tests for `*BlockService*` now uses `AbstractBlockServiceTestCase`

### Deprecated
- Deprecate empty class `BaseTestBlockService`
- Deprecate `Tests\Block\AbstractBlockServiceTest` in favor of `Test\AbstractBlockServiceTestCase`

### Fixed
- Profiler block design for Symfony Profiler v2

### Removed
- Internal test classes are now excluded from the auto-loader

## [3.0.1](https://github.com/sonata-project/SonataBlockBundle/compare/3.0.0...3.0.1) - 2016-06-14
### Changed
- The log level on exceptions in `BlockRenderer` is decreased from critical to error
- Replaced profiler icon with existing icon from profiler toolbar

### Fixed
- Error with the default extension configuration for `config:dump-reference` command

### Removed
- Removed the asterisk sign from the profiler toolbar to be compliant with Symfony standard
