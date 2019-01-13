Annotations
===========

All annotations require jms/di-extra-bundle, it can easily be installed by composer:

.. code-block:: bash

    composer require jms/di-extra-bundle

if you want to know more: http://jmsyst.com/bundles/JMSDiExtraBundle

.. code-block:: yaml

    # config/packages/jms_di_extra.yaml

    jms_di_extra:
        annotation_patterns:
            - JMS\DiExtraBundle\Annotation
            - Sonata\BlockBundle\Annotation

Define Blocks
^^^^^^^^^^^^^

All you have to do is include ``Sonata\BlockBundle\Annotation`` and define the values you need::

    namespace AcmeBundle\Block;

    use Sonata\BlockBundle\Block\Service\AbstractBlockService;
    use Sonata\BlockBundle\Annotation as Sonata;

    /**
     * @Sonata\Block()
     */
    class MyBlock extends AbstractBlockService
    {
    }

.. note::

    If you need to define custom controllers you can also use jms/di-extra-bundle by using
    the DI\Service annotation.
