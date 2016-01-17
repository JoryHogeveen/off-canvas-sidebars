=== Off-Canvas Sidebars ===
Contributors: keraweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YGPLMLU7XQ9E8&lc=NL&item_name=Off%2dCanvas%20Sidebars&item_number=JWPP%2dOCS&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: genesis, off-canvas, sidebars, slidebars, jQuery, app, mobile, tablet, responsive
Requires at least: 3.8
Tested up to: 4.4
Stable tag: 0.1

Add off-canvas sidebars using the Slidebars jQuery plugin.

== Description ==

= Overview / Features =

*	Add off-canvas sidebars to the left and right of your website
*	You can add the control buttons with a widget, a menu item or with custom code, [click here for documentation](http://plugins.adchsm.me/slidebars/usage.php "click here for documentation").
*	Various customisation options under the Appearances menu

= Compatibility =

This plugin should work with most themes and plugins allthough I can't be sure for all use-cases. At this point it's still a 0.x version...
Themes based on the Genesis Framework are supported by default. Please read the installation instructions for other themes!

= It's not working! / I found a bug! =

Please let me know through the support and add a plugins and themes list! :)

= Credits =

*	Slidebars jQuery plugin by [Adam](http://plugins.adchsm.me/slidebars/ "Adam"), thank you for this great plugin!

== Installation ==

Installation of this plugin works like any other plugin out there. Either:

1. Upload the zip file to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

Or search for "Off-Canvas Sidebars" via your plugins menu.

= Theme setup =

**Themes based on the Genesis Framework are supported by default! No theme changes needed.**

Add this code directly after the <body>. This is probably located in the header.php or index.php theme file.
`<?php do_action('website_before'); ?>`

Add this code directly after the site content, before the wp_footer() function. This is probably located in the footer.php or index.php theme file.
`<?php do_action('website_after'); ?>`
*Important: This code needs to be a direct child of the <body>!*

The final output of your theme should be similar to this:
`<html>
	<head>
		** HEADER STUFF **
	</head>
	<body>
		<?php do_action('website_before'); ?>
		** WEBSITE CONTENT **
		<?php do_action('website_after'); ?>
		<?php wp_footer(); ?>
	</body>
</html>`

= Custom theme setup =

*	Please [click here for documentation](http://plugins.adchsm.me/slidebars/usage.php "click here for documentation").
*	Disable the front-end option in the settings page under the Appearances menu.
*	Scripts and styles will still be included!

== Screenshots ==

1. Settings page
2. Control Widget
3. Menu item
4. Sidebar left (Push effect) -> image from Slidebars website
5. Sidebar right (Overlay effect) -> image from Slidebars website

== Changelog ==

= 0.1 =

Created from nothingness just to be one of the cool kids. Yay!

== Other Notes ==

You can find me here:

*	[Keraweb](http://www.keraweb.nl/ "Keraweb")
*	[LinkedIn](https://nl.linkedin.com/in/joryhogeveen "LinkedIn profile")

= Credits =

*	Slidebars jQuery plugin by [Adam](http://plugins.adchsm.me/slidebars/ "Adam"), thank you for this great plugin!

= Ideas? =

Please let me know through the support page!
