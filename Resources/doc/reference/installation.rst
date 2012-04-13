Installation
============

To begin, add the dependent bundles to the vendor/bundles directory. Add the following lines to the file deps::

    [SonataBlockBundle]
        git=http://github.com/sonata-project/SonataBlockBundle.git
        target=/bundles/Sonata/BlockBundle
        version=origin/2.0

Now, add the bundle to the kernel

.. code-block:: php

    <?php
    public function registerbundles()
    {
        return array(
            // Vendor specifics bundles
            new Sonata\BlockBundle\SonataBlockBundle(),
        );
    }

Update the ``autoload.php`` to add new namespaces:

.. code-block:: php

    <?php
    $loader->registerNamespaces(array(
        'Sonata'                             => __DIR__,

        // ... other declarations
    ));

Configuration
-------------

To use the ``BlockBundle``, add the following lines to your application configuration
file.

.. code-block:: yaml

    # app/config/config.yml
    sonata_block:
        default_contexts: [cms]
        blocks:
            sonata.admin.block.admin_list:
                contexts:   [admin]

            #sonata.admin_doctrine_orm.block.audit:
            #    contexts:   [admin]

            sonata.block.service.text:
            sonata.block.service.action:
            sonata.block.service.rss:

            # Some specific block from the SonataMediaBundle
            #sonata.media.block.media:
            #sonata.media.block.gallery:
            #sonata.media.block.feature_media: