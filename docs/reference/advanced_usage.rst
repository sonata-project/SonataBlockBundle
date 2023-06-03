.. index::
    single: Advanced
    single: Usage
    single: Context

Advanced usage
==============

This happens when a block is rendered:

* A block is loaded based on the configuration passed to ``sonata_block_render``,
* Each block model also has a block service, and its ``execute`` method is called:

  * You can logic into block service's ``execute`` method, like in a controller,
  * It renders a template,
  * It returns a `Response` object.

Block loading
-------------

Block models are loaded by a chain loader. You should be able to add your own loader by tagging a service with ``sonata.block.loader"`` and implementing ``Sonata\BlockBundle\Block\BlockLoaderInterface`` in the loader class.

Empty block
-----------

By default, the loader interface expects the exception ``Sonata\BlockBundle\Exception\BlockNotFoundException`` if a block is not found.
Return an empty block from your loader class if the default behavior for your blocks is to always return content.
