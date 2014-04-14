.. index::
    single: Installation
    single: Configuration

Installation
============

To begin, add the dependent bundles to the vendor/bundles directory. Add the following lines to the file deps:

.. code-block:: bash

    php composer.phar require sonata-project/block-bundle

You’ll be asked to type in a version constraint. 'dev-master' will get you the latest
version, compatible with the latest Symfony2 version. Check `packagist <https://packagist.org/packages/sonata-project/block-bundle>`_
for older versions:

.. code-block:: bash

    Please provide a version constraint for the sonata-project/block-bundle requirement: dev-master

Now, add the bundle to the kernel:

.. code-block:: php

    <?php

    // app/AppKernel.php

    public function registerbundles()
    {
        return array(
            // Dependency (check that you don't have already this line)
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            // Vendor specifics bundles
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
        );
    }

Some features provided by this Bundle require the ``SonataAdminBundle``.
Please add an explicit required dependency to your project's `composer.json` to the ``SonataAdminBundle`` with the version listed in the suggestion of this Bundle.

Configuration
-------------

To use the ``BlockBundle``, add the following lines to your application configuration file:

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
            sonata.block.service.rss:

            # Some specific block from the SonataMediaBundle
            #sonata.media.block.media:
            #sonata.media.block.gallery:
            #sonata.media.block.feature_media:
