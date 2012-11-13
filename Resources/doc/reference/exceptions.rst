Exception strategy
==================

Any exception thrown by a block service is handled by an exception strategy manager that
determines how the exception should be handled. A default strategy can be defined for all
block types but a specific strategy can also be defined on a per block type basis.

The exception strategy uses an exception filter and an exception renderer.

Filters
-------

The role of an exception filter is to define which exceptions should be handled and which
should be ignored silently. There are currently 4 filters available:

    - Debug only: only handle exceptions when in debug mode. (default)
    - Ignore block exception: only handle exceptions that don't implement ``BlockExceptionInterface``
    - Keep all: handle all exceptions
    - Keep non: ignore all exceptions (use with care)

These filters may be modified or completed with others filters in the configuration file:

.. code-block:: yaml

    #config.yml
    sonata_block:
        exception:
            default:
                filter:                     debug_only
            filters:
                debug_only:             sonata.block.exception.filter.debug_only
                ignore_block_exception: sonata.block.exception.filter.ignore_block_exception
                keep_all:               sonata.block.exception.filter.keep_all
                keep_none:              sonata.block.exception.filter.keep_none

A default filter may be configure to apply, by default, to all block types. If you wish to
customize a filter on a particular block type, you may also add the following option in the
configuration file:

.. code-block:: yaml

    #config.yml
    sonata_block:
        blocks:
            sonata.block.service.text:
                exception: { filter: keep_all }

Renderers
---------

The role of an exception renderer is to define what to do with the exceptions that have passed
the filter. There are currently 2 renderers available:

    - inline: renders a twig template within the rendering workflow with minimal information regarding the exception.
    - inline_debug: renders a twig template with the full debug exception information from symfony.
    - throw: throws the exception to let the framework handle the exception.

These filters may be modified or completed with others filters in the configuration file:

.. code-block:: yaml

    #config.yml
    sonata_block:
        exception:
            default:
                renderer:               throw
            renderers:
                inline:                 sonata.block.exception.renderer.inline
                inline_debug:           sonata.block.exception.renderer.inline_debug
                throw:                  sonata.block.exception.renderer.throw


A default renderer may be configure to apply, by default, to all block types. If you wish to
customize a renderer on a particular block type, you may also add the following option in the
configuration file:

.. code-block:: yaml

    #config.yml
    sonata_block:
        blocks:
            sonata.block.service.text:
                exception: { renderer: inline }