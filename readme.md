# oik-batchmove 
Contributors: bobbingwide
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: category, republish, add, update, delete, scheduled republish
Requires at least: 3.5.1
Tested up to: 3.8.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
This plugin enables you to filter posts in a particular category and then apply mass updates to selected posts.

Actions supported:

* Update to selected category - Delete the currently filtered category and set a new Target Category for the selected posts
* Add selected category - Adds the Target Category to the selected posts
* Delete selected category - Deletes the currently filtered category from the selected posts
* Republish - update the post date to the current time stamp.
* Republish - alter the post date by adding or subtracting defined amounts

For version 2.0, oik batchmove also supports Scheduled republishing using WordPress CRON


## Installation 
1. Upload the contents of the oik-batchmove plugin to the `/wp-content/plugins/oik-batchmove' directory
1. Activate the oik-batchmove plugin through the 'Plugins' menu in WordPress
1. Visit Posts > Batch move or Oik options > Batch move to start changing post's categories or publication dates.


## Frequently Asked Questions 
# How do I use it? 

1. Visit Posts > Batch move
1. Use the Selection criteria to list the posts you may want to alter and click on Filter.
1. Select the posts to change.
1. Select the target category, choose the Action to perform, click on Apply changes.

# How do I use scheduled republishing? 

To enable scheduled republishing:
1. Activate the oik-batchmove plugin
1. Visit oik options > Scheduled republish to enter the settings you want to use.
1. Ensure Activated? is checked
1. Click on Update

This will enable the WordPress CRON scheduling to perform scheduled republishing on a daily basis.

To disable scheduled republishing:

1. With the oik-batchmove plugin activated
1. Visit oik options > Scheduled republish
1. Ensure Activated? is unchecked
1. Click on Update

This will stop the WordPress CRON scheduling from performing scheduled republishing.

# How do I see what's going to be republished? 
The "Reposts for today" box shows the posts that have not yet been republished.
When Scheduled republish is not activated then this will list the posts that were published on the date determined by applying "look back" to the current date.
When Scheduled republish is activated you would not expect to see any posts until you change the "look back" value.

The "Reposts for tomorrow" box shows you the posts that may be republished in the next scheduled invocation.

# How do I see what's been republished? 
The "Rescheduled posts" box lists posts with a post date of ( today - "look back" + "reschedule" )
If you use the default values '-450 days' and '+451 days' this would be the posts scheduled for publishing tomorrow.
If you find a post that you don't want republished then you may want to edit or delete it.

# My time zone is not GMT (UTC+0)
This scenario has not been tested.

# How can I exclude posts? 
@TODO This is a planned feature

# Will my posts be re-publicized to Facebook and Twitter? 
Yes. That's the plan.
We're currently testing with Jetpack publicize.
Let us know which other social media sharing/publicize plugins you want supported.


# oik-batchmove may not be fully functional 
I get a message that says:
*oik-batchmove may not be fully functional*. Please install and activate the required version of this plugin: oik version 2.0

This message is asking you to install and activate the oik base plugin. There should be a link:

* Install oik - this means that oik is not installed. Click on the link to download the latest version of oik
* Activate oik base plugin - this means that the oik base plugin is not activated. Click on the link to activate the installed version of oik
* Upgrade oik - this means that the activated version of oik is not at or higher than the required level. Click on the link to update to the latest version of oik

If you do not perform the action then the plugin won't work properly.

# Why do I need oik? 
The oik base plugin provides APIs (Application Programming Interfaces) which deliver 90% of the functionality that makes this plugin work.
oik provides a lot of stuff, which is mostly dormant until you really need it.

# Which version of oik do I need? 
The oik-batchmove plugin is dependent upon oik (v2.0 or higher) for date filter logic

# Are there similar plugins? 
Yes. This plugin was sponsored by Howard Popeck for Our Listeners Club - for the music loving audiophile
Before developing oik-batchmove I tried these plugins. They didn't satisfy the original requirements.

* [Batch-Move wp plugin](http://wordpress.org/plugins/batchmove/)
* [Bulk Move](http://wordpress.org/plugins/bulk-move/)

I'm not aware of any other plugins that perform Scheduled republishing logic.

## Screenshots 
1. Selection criteria: Choose the posts to alter
2. Selected posts
3. Target category and Action
4. Result reporting
5. Redisplaying a republished post
6. Scheduled republish options
7. Reposts for today - BEFORE scheduled publish has run. e.g. when scheduled processing not activated
8. Reposts for tomorrow - a look ahead to posts that will be republished tomorrow
9. Rescheduled posts and CRON - when no posts are scheduled for tomorrow
10. Rescheduled posts - posts which are scheduled for publishing tomorrow
11. CRON box - showing next scheduled time and information for the most recent run

## Upgrade Notice 
# 2.0 
Now dependent upon oik v2.1

# 2.0-beta.0107 
Version on oik-plugins sites is dependent upon oik v2.1-beta

# 2.0-alpha.1218 
New version with "Scheduled republish" capability. Once again sponsored by Howard Popeck

# 1.0 
First version for WordPress.org. Dependent upon oik v2.0 and above

# 0.1.0625 
Now dependent upon PHP 5.3 for date adjustment and oik v2.0

# 0.1.0305 
Now dependent upon oik v2.0-alpha - to use the date filter logic

# 0.1.0221 
This plugin is dependent upon the oik base plugin.
The minimum version supported is oiv v1.17.
The date filter logic is dependent upon oik v1.18 or higher.

# 0.1.0218 
This plugin is dependent upon the oik base plugin v1.17

## Changelog 
# 2.0 
* Changed: New option "reschedule_time" allows you to set the publishing time to a fixed time. e.g. 09:00:00. Defaults to republishing with the original time.
* Changed: post_gmt_date also adjusted in manual republish

# 2.0-beta.0107 
* Fixed: problem with missing/duplicate function bw_update_option()
* Tested: with WordPress 3.8
* Added: screen captures for Scheduled republish

# 2.0-alpha.1218 
* Added: 'Scheduled republish' capability with its own admin page and WordPress CRON scheduling
* Changed: Now displays comment count on the Batch move page
* Changed: Posts > Batch move is now available to users with 'manage_categories' capability
* Changed: bw_date_adjust() function moved to oik-batchmove.php

# 1.0 
* Changed: Removed call to oik_register_plugin_server() for publication on WordPress.org

# 0.1.0625 
* Added: Can now filter All categories - for when you want to filter and republish based on date alone
* Changed: Date filter now allows selection of a single day
* Changed: Plugin initialisation when file loaded now implemented as a function oik_batchmove_plugin_loaded()

# 0.1.0625 
* Added: Date adjustments can now be applied when using "republish". e.g. +2 years

# 0.1.0305 
* Changed: Post ID's in the select list are now links. This makes it a bit easier to decide whether or not to select it

# 0.1.0221 
* Added: Code to enable the select all check box
* Changed: Renamed some internal functions to prepare for future transition

# 0.1.0218 
* Added: New code supports listing of posts and actions to add a category, remove a category or update (replace) or to republish a post

## Further reading 
If you want to read more about the oik plugins then please visit the
[oik plugin](http://www.oik-plugins.com/oik)
**"the oik plugin - for often included key-information"**

