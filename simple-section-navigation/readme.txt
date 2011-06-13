=== simple Section Navigation Widget ===
Contributors: Jacob M Goldman (C. Murray Consulting)
Donate link: http://www.cmurrayconsulting.com/software/wordpress-simple-section-navigation/
Tags: navigation, section, cms, pages, top level, hierarchy
Requires at least: 2.7
Tested up to: 2.8.3
Stable tag: 1.3.1

Adds a widget to your sidebar for section based navigation. Essential for CMS implementations! The title of the 
widget is the top level page within the current section. Shows all page siblings (except on the top level page), 
all parents and grandparents (and higher), the siblings of all parents and grandparents (up to top level page), 
and any immediate children of the current page. Can also be called by a function inside template files. May 
exclude any pages or sections. Uses standard WordPress navigation classes for easy styling. Easy configuration.

== Description ==

Adds a widget to your sidebar for section based navigation. Essential for CMS implementations! 

The title of the widget is the top level page within the current section. Shows all page siblings (except on 
the top level page), all parents and grandparents (and higher), the siblings of all parents and grandparents 
(up to top level page), and any immediate children of the current page. Can also be called by a function inside 
template files.

It includes an easy to use configuration panel inside the WordPress settings menu. From this panel you can:

   1. Determine whether the section navigation widget should appear on the home page
   2. Override standard behavior and have the widget show all pages in the current section
   3. Determine whether the widget should appear even if the section only has one page (the top level)
   4. Provide a list of pages to exclude from the output
   5. Determine whether the section navigation should still appear when viewing excluded pages
   6. Determine whether the section title should be linked
   7. Determine page sort order (defaults to menu order)

The widget uses standard WordPress navigation classes, in addition to a unique class around the widget, for 
easy styling.

Compatible with WordPress MU.

Considerable under the hood changes in 1.2 fixes page flattening / depth issue by "excluding" instead of 
"including" pages.


== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted
folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the "Section Nav" menu item under "Settings"
4. Widget users can add it to the sidebar by going to the "Widgets" menu under "Appearance" and adding the "Simple
Section Nav" widget
5. Template authors can follow the instructions in the "Section Nav" menu to easily add it to a widget-less template.


== Screenshots ==

1. Sceenshot of output, using widget. "Get Informed" is a top level pages, and "Clinics" is a sub page.
2. Screenshot of configuration panel.


== Changelog ==

v1.1
* Added ability to link heading, which also wraps it in a unique id
* Improved excluded pages handling
* Ability to exclude entire sections from using the widget

v1.1.2
* Fixed occassional flattening or wrong order of hierarchical pages

v1.2
* DEFINITIVE FIX FOR PAGE FLATTENING / FLAT HIERARCHY / NO DEPTH ISSUES
* Performance improvements

v1.3
* Ability to set page sort order (still defaults to menu order)
* Applies current_page_item and current_page_ancestor classes to optional heading link
* Easy access to settings panel from plug-ins page
* WordPress 2.8 compatibility check

v1.3.1
* Fixes and optimizes output on posts page, posts, and archives

Future features:
* Lists private pages if user has permission to see them
* Ability to include an "Overview" link that links to top level page within section
* Ability to set a maximum page depth for display in widget
* Ability to treat blog categories as sub pages of blog home
* Light weight mode for sites with only 2 levels of pages