Provided Blocks
===============

Some block services are already provided. You may use them or check out the code to get ideas on how to create your own.

EmptyBlockService
-----------------

#TODO

TextBlockService
----------------

This block allows you to render anything you'd like. Be warned, the content you feed it with will be directly interpreted (which allows you to put in some HTML for instance).

Pretty straightforward, you need only to add the block service to your page and configure it with the content you'd like to see displayed in HTML.

RssBlockService
---------------

This block displays an RSS feed.

When you add this block, specify a title and an RSS URL and the last messages from the RSS feed will be displayed in your block.

Base template is ``SonataBlockBundle:Block:block_core_rss.html.twig`` but you may of course override it.

MenuBlockService
----------------

This block service displays a KNP Menu.

Upon configuration, you may set a KNP Menu name (as specified in `KnpMenuBundle documentation <https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/index.md#rendering-menus>`_), and some rendering options (see KNP Doc for those).

Set ``cache_policy`` to private if this menu is dedicated to be in a user part.
