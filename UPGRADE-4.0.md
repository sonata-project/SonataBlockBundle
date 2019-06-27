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

## Closed API

Many classes have been made final, meaning you can no longer extend them.
Consider using decoration instead.

  * `Sonata\BlockBundle\Annotation\Block`
  * `Sonata\BlockBundle\Block\BlockContext`
  * `Sonata\BlockBundle\Block\BlockContextManager`
  * `Sonata\BlockBundle\Block\BlockLoaderChain`
  * `Sonata\BlockBundle\Block\BlockRenderer`
  * `Sonata\BlockBundle\Block\BlockServiceManager`
  * `Sonata\BlockBundle\Block\Loader\ServiceLoader`
  * `Sonata\BlockBundle\Block\Service\ContainerBlockService`
  * `Sonata\BlockBundle\Block\Service\EmptyBlockService`
  * `Sonata\BlockBundle\Block\Service\MenuBlockService`
  * `Sonata\BlockBundle\Block\Service\RssBlockService`
  * `Sonata\BlockBundle\Block\Service\TemplateBlockService`
  * `Sonata\BlockBundle\Block\Service\TextBlockService`
  * `Sonata\BlockBundle\Cache\HttpCacheHandler`
  * `Sonata\BlockBundle\Cache\NoopHttpCacheHandler`
  * `Sonata\BlockBundle\Command\DebugBlocksCommand`
  * `Sonata\BlockBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass`
  * `Sonata\BlockBundle\DependencyInjection\Compiler\TweakCompilerPass`
  * `Sonata\BlockBundle\DependencyInjection\Configuration`
  * `Sonata\BlockBundle\DependencyInjection\SonataBlockExtension`
  * `Sonata\BlockBundle\Event\BlockEvent`
  * `Sonata\BlockBundle\Exception\BlockNotFoundException`
  * `Sonata\BlockBundle\Exception\BlockOptionsException`
  * `Sonata\BlockBundle\Exception\Filter\DebugOnlyFilter`
  * `Sonata\BlockBundle\Exception\Filter\IgnoreClassFilter`
  * `Sonata\BlockBundle\Exception\Filter\KeepAllFilter`
  * `Sonata\BlockBundle\Exception\Filter\KeepNoneFilter`
  * `Sonata\BlockBundle\Exception\Renderer\InlineDebugRenderer`
  * `Sonata\BlockBundle\Exception\Renderer\InlineRenderer`
  * `Sonata\BlockBundle\Exception\Renderer\MonkeyThrowRenderer`
  * `Sonata\BlockBundle\Exception\Strategy\StrategyManager`
  * `Sonata\BlockBundle\Form\Type\ContainerTemplateType`
  * `Sonata\BlockBundle\Form\Type\ServiceListType`
  * `Sonata\BlockBundle\Meta\Metadata`
  * `Sonata\BlockBundle\Model\EmptyBlock`
  * `Sonata\BlockBundle\Profiler\DataCollector\BlockDataCollector`
  * `Sonata\BlockBundle\SonataBlockBundle`
  * `Sonata\BlockBundle\Twig\Extension\BlockExtension`
  * `Sonata\BlockBundle\Twig\GlobalVariables`
  * `Sonata\BlockBundle\Util\RecursiveBlockIterator`
  * `Sonata\BlockBundle\Util\RecursiveBlockIteratorIterator`
