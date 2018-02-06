### HTMLPress ###
Contributors: surror
Tags: html snippet, simple html snippets, html code, html shortcode, shortcodes
Tested up to: 4.9.2
Stable tag: 0.1.0
Requires at least: 4.4

Simple HTML snippets generator and use it with shortcode.

## Description ##

Simple HTML snippets generator and use it with shortcode.

![HTMLPress Editor](http://surror.com/wp-content/uploads/2018/02/screenshot-1.gif)

## Installation ##

1. Download the plugin
2. Extract the contents of the zip file
3. Upload the contents of the zip file to the `wp-content/plugins/` folder of your WordPress installation
4. Activate the `HTMLPress` plugin from 'Plugins' page.

## Frequently Asked Questions ##

# How to use snippet? #
You can use it with shortcode. It'll work everywhere where the showtcode works.

# Not able to see front end editor? #
Goto `General -> Permalinks` setting and just save the settings. It re-generate the permalink structure.

# Where the snippets are stored? #
Snippets are stored in files not in database. In `/uploads/` direcotry the `/htmlpress/` directory it created. And store the files in post id directory. E.g. You have crate the module which id is `42` then the files are stored in uploads directory like `/uploads/htmlpress/42/`. Only .html, .js & .css files are created.

## Changelog ##

# 0.1.0 #
* Initial release.