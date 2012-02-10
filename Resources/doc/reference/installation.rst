Installation
============

To begin, add the dependent bundles to the vendor/bundles directory. Add the following lines to the file deps::

    [SonataBlockBundle]
        git=http://github.com/sonata-project/SonataBlockBundle.git
        target=/bundles/Sonata/BlockBundle

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
