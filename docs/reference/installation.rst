.. index::
    single: Installation
    single: Configuration

Installation
============

The easiest way to install ``SonataBlockBundle`` is to require it with Composer:

.. code-block:: bash

    $ composer require sonata-project/block-bundle


Now, enable the bundle in ``bundles.php`` file::

    // config/bundles.php

    return [
        // ...
        Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true],
        Sonata\CoreBundle\SonataCoreBundle::class => ['all' => true],
        Sonata\BlockBundle\SonataBlockBundle::class => ['all' => true],
    ];

Some features provided by this bundle require the ``SonataAdminBundle``.
Please add an explicit required dependency to your project's `composer.json` to
the ``SonataAdminBundle`` with the version listed in the suggestions of this Bundle.

Configuration
-------------

To use the ``BlockBundle``, add the following lines to your application configuration file:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_block.yaml

        sonata_block:
            default_contexts: [sonata_page_bundle]
            blocks:
                # Some block with different templates
                #acme.demo.block.demo:
                #    templates:
                #       - { name: 'Simple', template: '@AcmeDemo/Block/demo_simple.html.twig' }
                #       - { name: 'Big',    template: '@AcmeDemo/Block/demo_big.html.twig' }
