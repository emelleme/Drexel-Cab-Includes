=== Post Expirator ===
Contributors: axelseaa
Tags: expire, posts, pages
Requires at least: 2.5
Tested up to: 2.7
Stable tag: trunk

Allows you to add an expiration date (hourly) to posts which you can configure to either delete the post or change it to a draft.

== Description ==

The Post Expirator plugin allows the user to set expiration dates for both posts and pages.  There is a configuration option page in the plugins 
area that will allow you to seperataly control whether or not posts/pages are wither deleted or changed to draft status.

The plugin hooks into the wp cron processes and runs every hour.

The expiration date can be displayed within the actual post by using the [postexpirator] tag.  The format attribute will override the plugin 
default display format.  See the [PHP Date Function](http://us2.php.net/manual/en/function.date.php) for valid date/time format options. 

**[postexpirator] shortcode attributes**

* type - defaults to full - valid options are full,date,time
* dateformat - format set here will override the value set on the settings page
* timeformat - format set here will override the value set on the settings page 

== Wordpress MU ==

This plugin is compataibile with Wordpress MU 1.5+, however currently it will not work in the mu-plugins folder due to the plugin activation 
functions.

== Credits ==

Plugin is based on the orginial [Expiration Date](http://www.hostscope.com/wordpress-plugins/the-expirationdate-wordpress-plugin/) plugin by jrrl. 

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

**Version 1.3.1**

* Fixed sporadic issue of expired posts not being removed

**Version 1.3**

* Expiration date is now retained across all post status changes
* Modified date/time format options for shortcode postexpirator tag
* Added the ability to add text automatically to the post footer if expiration date is set

**Version 1.2.1**

* Fixed issue with display date format not being recognized after upgrade

**Version 1.2**

* Changed wording from "Expiration Date" to "Post Expirator" and moved the configuration options to the "Settings" tab.
* Added shortcode tag [postexpirator] to display the post expiration date within the post
** Added new setting for the default format
* Fixed bug where expiration date was removed when a post was auto saved

**Version 1.1**

* Expired posts retain expiration date

**Version 1.0**

* Initial Release

