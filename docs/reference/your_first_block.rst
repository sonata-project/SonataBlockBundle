.. index::
    single: Block
    single: Tutorial
    single: RSS Block

Your first block
================

This quick tutorial explains how to create an `RSS reader` block.

A `block service` is just a service which must implement the ``BlockServiceInterface`` interface. There is only one instance of a block service, however there are many block instances.

First namespaces
----------------

The ``AbstractBlockService`` implements some basic methods defined by the interface.
The current RSS block will extend this base class. The other `use` statements are required by the interface's remaining methods::

    namespace Sonata\BlockBundle\Block;

    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;
    use Sonata\BlockBundle\Block\BlockContextInterface;
    use Sonata\BlockBundle\Block\Service\AbstractBlockService;
    use Sonata\BlockBundle\Mapper\FormMapper;
    use Sonata\BlockBundle\Model\BlockInterface;
    use Sonata\Form\Validator\ErrorElement;

Default settings
----------------

A `block service` needs settings to work properly, so to ensure consistency, the service should define a ``configureOptions`` method.
In the current tutorial, the default settings are:

* `URL`: the feed url,
* `title`: the block title,
* `template`: the template to render the block.

.. code-block:: php

    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'url' => false,
            'title' => 'Insert the rss title',
            'template' => '@SonataBlock/Block/block_core_rss.html.twig',
        ]);
    }

Form Editing
------------
You can define an editing config the following way::

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper
            ->add('settings', 'sonata_type_immutable_array', [
                'keys' => [
                    ['url', 'url', ['required' => false]],
                    ['title', 'text', ['required' => false]],
                ]
            ])
        ;
    }

The validation is done at runtime through a ``validateBlock`` method. You can call any Symfony assertions, like::

    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        $errorElement
            ->with('settings.url')
                ->assertNotNull([])
                ->assertNotBlank()
            ->end()
            ->with('settings.title')
                ->assertNotNull([])
                ->assertNotBlank()
                ->assertMaxLength(['limit' => 50])
            ->end()
        ;
    }

The ``sonata_type_immutable_array`` type is a specific `form type` which allows to edit an array.

Execute
-------

The next step is to implement the `execute` method. This method must return a ``Response`` object, which is used to render the block::

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        // merge settings
        $settings = $blockContext->getSettings();
        $feeds = false;

        if ($settings['url']) {
            $options = [
                'http' => [
                    'user_agent' => 'Sonata/RSS Reader',
                    'timeout' => 2,
                ]
            ];

            // retrieve contents with a specific stream context to avoid php errors
            $content = @file_get_contents($settings['url'], false, stream_context_create($options));

            if ($content) {
                // generate a simple xml element
                try {
                    $feeds = new \SimpleXMLElement($content);
                    $feeds = $feeds->channel->item;
                } catch (\Exception $e) {
                    // silently fail error
                }
            }
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'feeds'     => $feeds,
            'block'     => $blockContext->getBlock(),
            'settings'  => $settings
        ], $response);
    }

Template
--------

In this tutorial, the block template is very simple. We loop through feeds, or if none are available, an error message is displayed.

.. code-block:: jinja

    {% extends sonata_block.templates.block_base %}

    {% block block %}
        <h3 class="sonata-feed-title">{{ settings.title }}</h3>

        <div class="sonata-feeds-container">
            {% for feed in feeds %}
                <div>
                    <strong><a href="{{ feed.link}}" rel="nofollow" title="{{ feed.title }}">{{ feed.title }}</a></strong>
                    <div>{{ feed.description|raw }}</div>
                </div>
            {% else %}
                    No feeds available.
            {% endfor %}
        </div>
    {% endblock %}

Service
-------

We are almost done! Now, just declare the block as a service:

.. configuration-block::

    .. code-block:: xml

        <!-- config/services.xml -->

        <service id="sonata.block.service.rss" class="Sonata\BlockBundle\Block\Service\RssBlockService">
            <tag name="sonata.block"/>
            <argument/>
            <argument type="service" id="twig"/>
        </service>

    .. code-block:: yaml

        # config/services.yaml

        services:
            sonata.block.service.rss:
                class: Sonata\BlockBundle\Block\Service\RssBlockService
                arguments:
                    - ~
                    - '@twig'
                tags:
                    - { name: sonata.block }

Then, add the service to Sonata configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/sonata_block.yaml

        sonata_block:
            blocks:
                sonata.block.service.rss: ~

If you want to set up caching, take a look at the SonataCacheBundle support documentation: :doc:`cache`.
