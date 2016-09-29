=== Off-Canvas Sidebars ===
Contributors: keraweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YGPLMLU7XQ9E8&lc=NL&item_name=Off%2dCanvas%20Sidebars&item_number=JWPP%2dOCS&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: genesis, off-canvas, sidebars, slidebars, jQuery, app, mobile, tablet, responsive
Requires at least: 3.8
Tested up to: 4.6
Stable tag: 0.3

Add off-canvas sidebars using the Slidebars jQuery plugin.

== Description ==

This plugin will add various options to implement off-canvas sidebars in your WordPress theme using the Slidebars jQuery plugin.

= Compatibility (IMPORTANT!) =

The structure of your theme is of great importance for this plugin. Please read the [installation guide](https://wordpress.org/plugins/off-canvas-sidebars/installation/) carefully!!

This plugin should work with most themes and plugins although I can't be sure for all use-cases. At this point it's still a 0.x version...
If the plugin does not work for your theme, please let me know through the support and add a plugins and themes list and I will take a look!

= Overview / Features =

*	Add off-canvas sidebars to the left, right, top and bottom of your website
*	You can add the control buttons with a widget, a menu item or with custom code, [click here for documentation](https://www.adchsm.com/slidebars/help/usage/ "click here for documentation")
*	Various customisation options under the Appearances menu

= It's not working! / I found a bug! =

Please let me know through the support and add a plugins and themes list! :)

= Credits =

*	Slidebars jQuery plugin by [Adam](https://www.adchsm.com/slidebars/ "Adam"), thank you for this great plugin!

== Installation ==

Installation of this plugin works like any other plugin out there. Either:

1. Upload the zip file to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

Or search for "Off-Canvas Sidebars" via your plugins menu.

= Theme Setup =

**Themes based on the Genesis Framework are supported by default! No changes needed.**

*Please note that it is possible that there are some Genesis themes that can not be supported due to their structure.*

There are more themes with similar implementations like Genesis to insert code before and after the website HTML through actions hooks.
[See a full list here with currently known themes](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Compatible-theme-hooks)

For other themes there are two options:
1. Simple theme setup
2. Custom theme setup

= 1: Simple theme setup =

First of all, I strongly advice to create a child theme if you didn't already! [Click here for more information](https://codex.wordpress.org/Child_Themes "Click here for more information").

Add this code directly after the &lt;body&gt; tag. This is probably located in the header.php or index.php theme file.
`<?php do_action('website_before'); ?>`

Add this code directly after the site content, before the wp_footer() function. This is probably located in the footer.php or index.php theme file.
`<?php do_action('website_after'); ?>`
*Important: This code needs to be a direct child of the &lt;body&gt; tag!*

The final output of your theme should be similar to this:
`<html>
	<head>
		** HEADER CONTENT **
	</head>
	<body>
		<?php do_action('website_before'); ?>
		** WEBSITE CONTENT **
		<?php do_action('website_after'); ?>
		<?php wp_footer(); ?>
	</body>
</html>`

= 2: Custom theme setup =

*	Disable the front-end option in the settings page under the Appearances menu.
*	Scripts and styles will still be included!
*	[Click here for documentation on the Slidebars jQuery plugin](https://www.adchsm.com/slidebars/help/usage/).
*	[Click here for the API and available actions and filters](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki)

== Screenshots ==

1. Settings page
2. Sidebars settings page (sidebars closed)
3. Sidebars settings page (sidebar opened)
4. Control Widget
5. Menu item
6. Sidebar left (Push effect) -> image from Slidebars website
7. Sidebar left (Overlay effect) -> image from Slidebars website
8. Sidebar top (Push effect) -> image from Slidebars website

== Changelog ==

= 0.3 =

*	Feature: Allow sidebars to overwrite some general settings
*	Feature: Option to set padding to sidebars
*	Feature: Option to choose other content types than only a WP sidebar for an off-canvas sidebar
*	Feature: Option to set your own CSS prefix (some classes are fixes to `ocs` and can't be changed, the prefix `ocs` is also the default prefix for new installations)
*	Feature: Added various actions, filters and JS hooks - [Click here for info](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki)
*	OCS API functions to output off canvas sidebars in your theme instead of using this plugin frontend functions - [Click here for info](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/PHP-API)
*	Fix: Sidebar ID validation wasn't correct

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/10)

= 0.2.2 =

*	Feature: Option to set the animation speed for sidebars
*	Feature: Option to use the FastClick library - [Click here for info](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/9 "Click here for info")
*	Fix: Disabling sidebars on global settings page didn't work

= 0.2.1 =

*	Fix: Add touch events for iOS mobile device compatibility
*	Added some actions for front-end (see Other Notes)

= 0.2.0.1 =

*	Fix: Global variable bug
*	UI: Improve settings page

= 0.2 =

*	Update Slidebars plugin to v2.0.2: [click here for info](https://www.adchsm.com/slidebars/features/ "Slidebars Features")
*	Feature: An unlimited amount of off-canvas sidebars (No longer just one left, one right)
*	Feature: 2 new locations (top and bottom)
*	Feature: 2 new effects (reveal and shift)
*	UI: Improved settings pages
*	I18n: Translations are now managed at [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/off-canvas-sidebars "translate.wordpress.org")
*	Screenshots updated
*	Tested with WordPress 4.6

= 0.1.2 =

*	Feature: First experiment for compatibility with fixed elements within the site container with the use of `transform: translateZ` (needed for `-webkit-` and `-moz-` only). [See problem here](http://stackoverflow.com/questions/2637058/positions-fixed-doesnt-work-when-using-webkit-transform "See problem here")
*	Improvement: Usage of a single instance of the class

= 0.1.1 =

*	Feature: Added the option to change the website_before and website_after hook names

= 0.1 =

Created from nothingness just to be one of the cool kids. Yay!

== Other Notes ==

You can find me here:

*	[GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/ "GitHub")
*	[Keraweb](http://www.keraweb.nl/ "Keraweb")
*	[LinkedIn](https://nl.linkedin.com/in/joryhogeveen "LinkedIn profile")

= Actions | Filters | API =

*	[See Wiki on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki)

= Credits =

*	Slidebars jQuery plugin by [Adam](https://www.adchsm.com/slidebars/ "Adam"), thank you for this great plugin!

= Ideas? =

Please let me know through the support page!

== Upgrade Notice ==

= 0.2 =
Version 0.2 introduces some radical code changes to the plugin. Please clear your cache after updating
