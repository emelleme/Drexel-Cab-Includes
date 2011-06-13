=== WP Smart Image ===
Contributors: Dario Ferrer (@metacortex)
Tags: images, image, thumbnail, photo, photos, layout, design, webdesign, picture, img
Donate link: http://www.darioferrer.com
Requires at least: 2.7
Tested up to: 2.8.6
Stable tag: 0.3.4

WP Smart Image is deprecated. Please change to WP Smart Image II.

== Description ==

**WP Smart Image is deprecated. Please change to [WP Smart Image II](http://wordpress.org/extend/plugins/wp-smart-image-ii/)**

*What is Wp Smart Image?*

WP Smart Image is the tool that combines the best image managment functionalities in WordPress and handles them to facilitate it's use.

If you are used to personalized fileds to assign images to posts, with WP Smart Image will forget all the extra work and will enjoy the real process automation that has been in WordPress all this time and probably you never knew existed.

*But what's the problem with Wordpress native image engine?*

No problem. The [WordPress](http://www.wordpress.org) image engine is one of the most advanced and flexible ones at the moment. WordPress offers a wide range of functionalities and tags, which allows for and easy handling of images and files, associating them with posts, categories, pages and other elements with total freedom.

However, the problem with all these funcionalities is that finding the propper way to associate data and get a specific result can get really hard. In an attempt to do that, many people have tried to do it by setting through custom fields, ignoring that WordPress has a large platform with many resources at our disposal to do that.

*What isn't Wp Smart Image?*

It is not a filter that works inside the content. WP Smart Image only works in the template areas and it's use is focused to the template's file system.

It is not a plugin that changes other functionalities behaviour. It's a resource that lets you take advantage of the already existing functionalities provided by WordPress.

Enjoy designing!

= Localization =

* French (fr_FR) - Valentin
* Russian (ru_RU) - Fat Cow
* Spanish (es_ES) - Dario Ferrer

If you have been translated this plugin into your language, please let me know.

== Installation ==

1. Upload `wp-smart-image` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php wp_smart_image(); ?>` in your template
1. You can add further customization throught `Settings > WP Smart Image Menu`

= Uninstall =

1. If you wish to remove the plugin completely, press button called "Remove data" to clean the Wordpress Smart Image DB table
1. Deactivate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= WP Smart Image works with custom fields? =

No. Using custom fields to show associated images is a wrong method. Yes, it is relatively famous, but is wrong. [WordPress](http://www.wordpress.org) has a very advanced ways to control post's images. However, saying is not the same than do it, because many times it is some difficult to achieve tha we want in this area. WP Smart Images simply makes this work a quite more easy to you.

= WP Smart Image adds many data in my DB? =

Noup. This plugins uses all existing post data you have been entered through Wordpress Editor. Only creates a little field in options table to save your settings.

= How can I settings my images to be shown? =

* Upload the images through your edition panel (required for database file association).
* In the Gallery section **drag the image you want to show to the first position**, then it will be shown, even if you don't use it in the content.
* That's it.

= Hey, I can't see the widget in my editor! =

Just activate the checkbox for option "Activate editor box" through Settings page. Save your settings.

= This new widget disables my previous settings? =

Absolutely not. Your old settings remains intacts.

= Can I customize the tag for better control of my layout? =

Yes. Please read the [first step guide](http://www.darioferrer.com/wp-smart-image) of WP Smart Image

= Where I must to place the tag? =

You should place the tag into [the loop](http://codex.wordpress.org/The_Loop) (see [screenshot #3](http://wordpress.org/extend/plugins/wp-smart-image/screenshot-3.png) for a graphic example)

= I can place the parameters in an unordered way? =

Yes, you can combine parameters without an specific order. Logically some parameters may not appear depending of your settings. For example, a "rel" attribute will not shown in an unlinked image, because "rel" is a property of links. In cases like this, if you set wrong parameters the plugin simply ignore them and works anyway.

= All parameters are right to all situations? =

You must to read the parameter's table to setting your combo correctly. If you have a Stric DTD site and if you activate a "target _blank", of course you'll ruin your standard. If you add a fixed ID's to several images or links, you are proceeding in a wrong way. 

Otherwise, if you are trying to implement some javascript/ajax toy defining "rel" or "id" parameters , you are in a good way to achieve what you want, depending of your intentions.

= Can I find direct support about WP Smart Image? =

Sure. You can find direct support at: [English users](http://www.darioferrer.com/que/viewforum.php?f=4) | [Spanish users](http://www.darioferrer.com/que/viewforum.php?f=2)

... and of course [starting a new topic](http://wordpress.org/tags/wp-smart-image?forum_id=10#postform) in Wordpress.org forum.

= "Dario, you forgot to add certain function..." =

Please let me know you're thinking through any of ways above. Thank you!.

= "I'm a programming guru and I think you can modify this string in this way..." =

All your suggestions are welcome. Thank you!.

== Screenshots ==

1. WP Smart Image - Settings
2. WP Smart Image - New `Parameters` tab.
3. Where to place the tag
4. New editor widget. Here you can choose easily the image to show.

== Changelog ==

= 0.3.3 =

* Bugfix: Orphan images are shown in widget editor on new posts.

= 0.3.2 =

* Added option to show random images instead of only one.
* Image widget is now available on Page editor.

= 0.3.1 =

* Fixed minor bug: widget appears even if option is unchecked.

= 0.3 =

* Added graphic interface for control images directly from article editor.
* Minor bugfixes.

= 0.2.3 =
* Added option for integrate Max Image Size Control plugin.
* Minor optimizations.

= 0.2.2 =
* Added option to include thumbnails in RSS feeds.
* Added new parameter `mode` to prepare function `wp_smart_image($mode='args')` to be passed by PHP parameters.
* Bugfix: Custom parameter `$target ='framename'` was changed to new one `$targetname = 'framename'` because old setting caused bad html parsing.

= 0.2.1 =
* Code optimization.
* Added french translation `fr_FR` (thanks Valentin!)

= 0.2 =
* General improvements.
* Added "width / height" image parameter.
* Added "rel" parameter to linked images.
* Added "target" parameter to linked images.
* Added "class / id" parameters to links (in addition to image class / id).
* Code optimization.
* Added reference table within the `parameters` tab (plugin Settings page).

= 0.1.2 =
* Some code optimization.
* Added russian translation `ru_RU` (thanks Fat Cow!).

= 0.1.1 =
* First public release

== Upgrade Notice ==

Please migrate your plugin to WP Smart Image II