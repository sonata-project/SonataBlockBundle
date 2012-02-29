Twig Helpers
============

Render a block from its instance

.. code-block:: jinja

    {{ sonata_block_render(block) }}

Render by providing the block's type and options

.. code-block:: jinja

    {{ sonata_block_render({
        'type': 'sonata.block.service.rss',
        'settings': {
            'title': 'Sonata Project\'s Feeds',
            'url': 'http://sonata-project.org/blog/archive.rss'
        }
    }) }}