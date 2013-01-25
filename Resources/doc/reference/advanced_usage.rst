Advanched usage
===============

This happens when a block is rendered:
    - a block is loaded based on the configuration passed to ``sonata_block_render``
    - if caching is configured, the cache is checked and content is returned if found
    - each block model also has a block service, the execute method of it is called:
        - you can put here logic like in a controller
        - it calls a template
        - the result is a Response object


Block loading
-------------

Block models are loaded by a chain loader, add your own loader by tagging a service with ``sonata.block.loader"`` and
implement ``Sonata\BlockBundle\Block\BlockLoaderInterface`` in the loader class.

Empty block
-----------

By default the loader interface expects the exception ``Sonata\BlockBundle\Exception\BlockNotFoundException`` if a block
is not found. Return an empty block from your loader class if the default behaviour for your blocks is to always return content.