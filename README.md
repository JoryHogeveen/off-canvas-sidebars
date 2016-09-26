# Off-Canvas Sidebars #
Add off-canvas sidebars using the Slidebars jQuery plugin.

[![WordPress Plugin version](https://img.shields.io/wordpress/plugin/v/off-canvas-sidebars.svg?style=flat)](https://wordpress.org/plugins/off-canvas-sidebars/)
[![WordPress Plugin WP tested version](https://img.shields.io/wordpress/v/off-canvas-sidebars.svg?style=flat)](https://wordpress.org/plugins/off-canvas-sidebars/)
[![WordPress Plugin downloads](https://img.shields.io/wordpress/plugin/dt/off-canvas-sidebars.svg?style=flat)](https://wordpress.org/plugins/off-canvas-sidebars/)
[![WordPress Plugin rating](https://img.shields.io/wordpress/plugin/r/off-canvas-sidebars.svg?style=flat)](https://wordpress.org/plugins/off-canvas-sidebars/)
[![Travis](https://secure.travis-ci.org/JoryHogeveen/off-canvas-sidebars.png?branch=master)](http://travis-ci.org/JoryHogeveen/off-canvas-sidebars)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](https://github.com/JoryHogeveen/off-canvas-sidebars/blob/master/license.txt)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=YGPLMLU7XQ9E8&lc=NL&item_name=Off%2dCanvas%20Sidebars&item_number=JWPP%2dOCS&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)

## Description

This plugin will add various options to implement off-canvas sidebars in your WordPress theme using the Slidebars jQuery plugin.

### Compatibility (IMPORTANT!)

The structure of your theme is of great importance for this plugin. Please read the installation guide carefully!!

*Most themes based on the Genesis Framework are supported by default. Please read the installation instructions for other themes!*

This plugin should work with most themes and plugins although I can't be sure for all use-cases. At this point it's still a 0.x version...
If the plugin does not work for your theme, please let me know through the support and add a plugins and themes list and I will take a look!

### Overview / Features

*	Add off-canvas sidebars to the left, right, top and bottom of your website
*	You can add the control buttons with a widget, a menu item or with custom code, [click here for documentation](https://www.adchsm.com/slidebars/help/usage/ "click here for documentation")
*	Various customisation options under the Appearances menu

### It's not working! / I found a bug!

Please let me know through the support and add a plugins and themes list! :)

### Credits

*	Slidebars jQuery plugin by [Adam](https://www.adchsm.com/slidebars/ "Adam"), thank you for this great plugin!

## Installation

Installation of this plugin works like any other plugin out there. Either:

1. Upload the zip file to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

Or search for "Off-Canvas Sidebars" via your plugins menu.

### Theme setup

**Themes based on the Genesis Framework are supported by default! No changes needed.**

*Please note that it is possible that there are some Genesis themes that can not be supported due to their structure.*

First of all, I strongly advice to create a child theme if you didn't already! [Click here for more information](https://codex.wordpress.org/Child_Themes "Click here for more information").

Add this code directly after the &lt;body&gt; tag. This is probably located in the header.php or index.php theme file.
`<?php do_action('website_before'); ?>`

Add this code directly after the site content, before the wp_footer() function. This is probably located in the footer.php or index.php theme file.
`<?php do_action('website_after'); ?>`
*Important: This code needs to be a direct child of the &lt;body&gt; tag!*

The final output of your theme should be similar to this:
```
<html>
	<head>
		** HEADER CONTENT **
	</head>
	<body>
		<?php do_action('website_before'); ?>
		** WEBSITE CONTENT **
		<?php do_action('website_after'); ?>
		<?php wp_footer(); ?>
	</body>
</html>
```

### Custom theme setup

*	Please [click here for documentation](https://www.adchsm.com/slidebars/help/usage/ "click here for documentation").
*	Disable the front-end option in the settings page under the Appearances menu.
*	Scripts and styles will still be included!
*	[Click here for info on available actions and filters](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Actions-&-Filters "Click here for info on available actions and filters")
*	[Click here for info on available API functions](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/API-functions "Click here for info on available API functions")
