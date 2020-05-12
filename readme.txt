=== Off-Canvas Sidebars & Menus (Slidebars) ===
Contributors: keraweb
Donate link: https://www.keraweb.nl/donate.php?for=off-canvas-sidebars
Tags: off-canvas, menus, widgets, sidebars, slidebars, panels, jQuery, app, mobile, tablet, responsive, genesis
Requires at least: 4.1
Tested up to: 5.4
Requires PHP: 5.2.4
Stable tag: 0.5.5

Add off-canvas sidebars (Slidebars) containing widgets, menus or other content using the Slidebars jQuery plugin.

== Description ==

This plugin will add various options to implement off-canvas sidebars in your WordPress theme based on the Slidebars jQuery plugin.

= Overview / Features =

*	Add off-canvas sidebars to the left, right, top and bottom of your website.
*	Use sidebar areas (widget-ready areas), menu locations or [custom hooks](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Actions-&-Filters) to place content into the off-canvas sidebars.
*	You can add control buttons with a widget, menu item, [shortcode](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Shortcodes) or with [custom code](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Theme-setup).
*	Various customisation options and settings available in the Appearances menu.
*	Extensive API for [PHP](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/PHP-API) and [JS](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/JavaScript-API).

= Compatibility (IMPORTANT!) =

The structure of your theme is of great importance for this plugin. Please read the [installation guide](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Theme-setup) carefully!!

This plugin should work with most themes and plugins although I can't be sure for all use-cases. Also note that at this point it's still a 0.x version...
If the plugin does not work for your theme, please let me know through the support and add a plugins and themes list and I will take a look!

**Fixed elements (like sticky menu's)**  
There are known issues with fixed elements and Slidebars. [Click here for more information](https://www.adchsm.com/slidebars/help/advanced-usage/elements-with-fixed-positions/)  
I've created two possible solutions for this:

1. Legacy CSS solution. Use basic CSS2 positioning instead of CSS3 transform with hardware acceleration.
2. JavaScript solution. It is slower but still allows the use of hardware acceleration for animations.

= It's not working! / I found a bug! =

Please let me know through [support](https://wordpress.org/support/plugin/off-canvas-sidebars) and add a plugins and themes list! :)  
Or [submit an issue here on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/issues)

= Credits =

*	Slidebars jQuery plugin by [Adam](https://www.adchsm.com/slidebars/ "Adam"), thank you for this great plugin!

== Installation ==

Installation of this plugin works like any other plugin out there. Either:

1. Upload the zip file to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

Or search for "Off-Canvas Sidebars" via your plugins menu.

= Theme Setup =

Off-Canvas Sidebars won't work "out of the box" with most themes, please read the documentation!
[Click here for theme setup documentation.](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Theme-setup)

== Frequently Asked Questions ==

= 1. Will this plugin work with any theme? =

No, due to the difference in structure not all theme's are compatible.
Though most themes can be *made* compatible with some modifications!

= 2. Can I add as many sidebars as I like? =

Yes you can, keep in mind that the more sidebars you add the heavier the load on the server (PHP) and browser (JS) will be.

= 3. How do I change the CSS for the sidebars? =

This plugin only provides the framework that handles the off-canvas part.
There are some settings that slightly change the display but this is very limited.
For more advanced customisations either:
1. Edit your theme style.css file (usually located in `/wp-content/themes/YOURTHEME/`).
2. Use a plugin such as [Simple Custom CSS](https://wordpress.org/plugins/simple-custom-css/).

== Screenshots ==

1. Settings page
2. Sidebars settings page (sidebars closed)
3. Sidebars settings page (sidebar opened)
4. Shortcode generator page
5. Control Widget
6. Menu item
7. Sidebar left (Push effect) -> image from Slidebars website
8. Sidebar left (Overlay effect) -> image from Slidebars website
9. Sidebar top (Push effect) -> image from Slidebars website

== Changelog ==

= 0.5.5 =

*	**Compatibility**: Changed the CSS to be compatible with `position: sticky;` elements. [#83](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/83)
*	**Compatibility**: Fix default before and after widget args. Widget element changed to `<div>` instead of `<li>`. [#88](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/88)
*	**Enhancement**: Late init option. [#81](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/81)
*	**Enhancement**: Allow minimum value of 0 for numeric settings.
*	**Fix**: Gutenberg notice that OCS couldn't be initialized.
*	**UI**: Rename control elements to triggers (uniform names).

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/84)

= 0.5.4 =

*	**Compatibility:** Support `wp_body_open` as `website_before` hook introduced in WordPress 5.2. [#70](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/70)
*	**Enhancement:** Allow Genesis Framework hooks to be overwritten in respect for `wp_body_open`.
*	**Enhancement:** Use CSS `vh` unit for canvas container min-height instead of JavaScript.
*	**Enhancement:** Better use of WP core UI elements.
*	**Enhancement:** Code quality improvements.

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/71)

= 0.5.3 =

*	**Enhancement:** Don't render triggers of disabled sidebars if auto-hide is enabled. [#56](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/56)
*	**Enhancement:** Support `wp_footer` as `website_after` hook. (Not compatible with all themes!)
*	**Enhancement:** Filters to overwrite `website_before` and `website_after` hook priorities.
*	**Enhancement:** Do not render inactive trigger buttons if auto-hide is enabled.

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/58)

= 0.5.2 =

*	**Feature:** New filter: `ocs_is_sidebar_enabled` to filter whether a sidebar should be rendered or not.
*	**Enhancement/Refactoring:** Class autoloader.

= 0.5.1 =

*	**Fix:** Bug with creating sidebars on a new installation. [#52](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/52)
*	**Enhancement/Refactoring:** Control trigger rendering.

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/53)

= 0.5 =

*	**Feature:** Make closing the slidebar after clicking a link an option. [#47](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/47) & [#48](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/48)
*	**Feature:** Add icon options in control trigger API and shortcode.
*	**Feature:** Enhance widget UI with advanced options for control triggers. [#27](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/27)
*	**Enhancement:** Setting page JS and UI enhancements.
*	**Enhancement:** Use data attributes to conform [W3C HTML5 specifications](http://www.w3.org/TR/html5/dom.html#embedding-custom-non-visible-data-with-the-data-*-attributes). [#50](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/50)
*	**Enhancement:** Add support links on plugins overview page.
*	**Refactoring:** Separate classes for tabs, page and settings/validation. Enhances plugin option validation. [#38](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/38)
*	**Refactoring:** Global OCS_DOMAIN constant.
*	**Updated/Added:** Screenshots.

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/36)

= 0.4.2 =

*	**Enhancement:** Keep scrollbar visible when scroll lock is active. [#44](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/44) & [PR #45](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/45)
*	**Enhancement:** (Slidebars library) Make sure that percentage based widths are rounded to actual pixels to prevent 1px differences on display.
*	**Fix:** PHP Notice on `fixed_elements` key.
*	**Compatibility:** Tested with WordPress 4.9

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/43)

= 0.4.1 =

*	**Fix:** Loading the correct menu in an off-canvas sidebar was not working correctly. [#37](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/37)
*	**Fix:** Scroll lock feature with CSS instead of JavaScript. [#39](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/39)
*	**Enhancement:** Add active sidebar ID to the html element classes. [#41](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/41)

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/40)

= 0.4 =

*	**Feature:** `[ocs_trigger]` shortcode to display trigger buttons/elements anywhere you like, [click here for documentation](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/Shortcodes). [#24](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/24)
	*	Shortcode generator available in the settings page.
	*	Integrate a shortcode generator with the WP Editor. [#32](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/32)
*	**Enhancement:** New "Legacy CSS" mode. Modified the Slidebars library to support older CSS2 animations. Can fix a lot of issues with fixed elements. [#26](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/26)
*	**Compatibility:** Modified the default Slidebars CSS to support anchor links and common smooth scroll implementations.
*	**Compatibility:** Some enhancements for compatibility with the WP Admin Bar.
*	**UI:** Improve widget UI [#27](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/27)
*	**Fix:** Enhance the codebase to be more aligned with the WP coding standards with CodeClimate.

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/25)

= 0.3.1.1 =

*	**Fix:** Settings page checkbox bug when saving

= 0.3.1 =

*	**Feature:** Allow changing this plugin capability to show the settings page
*	**Fix:** Update fixed element compat for the new Slidebars version (still experimental, Slidebars still doesn't fully support fixed elements within the site container)
*	**Fix:** Don't echo empty sidebar CSS selectors if no styles are set
*	**UI:** Set `.ocs-button` to `cursor: pointer;` by default
*	Update textdomain hook

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/23)

= 0.3 =

*	**Feature:** Allow sidebars to overwrite some general settings
*	**Feature:** Option to set padding to sidebars
*	**Feature:** Option to choose other content types than only a WP sidebar for an off-canvas sidebar
*	**Feature:** Option to set your own CSS prefix (some classes are fixes to `ocs` and can't be changed, the prefix `ocs` is also the default prefix for new installations)
*	**Feature:** Added various actions, filters and JS hooks - [Click here for info](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki)
*	**Feature:** OCS API functions to output off canvas sidebars in your theme instead of using this plugin frontend functions - [Click here for info](https://github.com/JoryHogeveen/off-canvas-sidebars/wiki/PHP-API)
*	**Fix:** Sidebar ID validation wasn't correct

Detailed info: [PR on GitHub](https://github.com/JoryHogeveen/off-canvas-sidebars/pull/10)

= 0.2.2 =

*	**Feature:** Option to set the animation speed for sidebars
*	**Feature:** Option to use the FastClick library - [Click here for info](https://github.com/JoryHogeveen/off-canvas-sidebars/issues/9 "Click here for info")
*	**Fix:** Disabling sidebars on global settings page didn't work

= 0.2.1 =

*	**Fix:** Add touch events for iOS mobile device compatibility
*	**Enhancement:** Added some actions for front-end (see Other Notes)

= 0.2.0.1 =

*	**Fix:** Global variable bug
*	**UI:** Improve settings page

= 0.2 =

*	Update Slidebars plugin to v2.0.2: [click here for info](https://www.adchsm.com/slidebars/features/ "Slidebars Features")
*	**Feature:** An unlimited amount of off-canvas sidebars (No longer just one left, one right)
*	**Feature:** 2 new locations (top and bottom)
*	**Feature:** 2 new effects (reveal and shift)
*	**UI:** Improved settings pages
*	**I18n:** Translations are now managed at [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/off-canvas-sidebars "translate.wordpress.org")
*	Screenshots updated
*	Tested with WordPress 4.6

= 0.1.2 =

*	**Feature:** First experiment for compatibility with fixed elements within the site container with the use of `transform: translateZ` (needed for `-webkit-` and `-moz-` only). [See problem here](http://stackoverflow.com/questions/2637058/positions-fixed-doesnt-work-when-using-webkit-transform "See problem here")
*	**Improvement:** Usage of a single instance of the class

= 0.1.1 =

*	**Feature:** Added the option to change the website_before and website_after hook names

= 0.1 =

Created from nothingness just to be one of the cool kids. Yay!

== Other Notes ==

= You can find me here: =

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

= 0.4 =
Version 0.4 introduces some radical code changes to the plugin. Please clear your cache after updating

= 0.2 =
Version 0.2 introduces some radical code changes to the plugin. Please clear your cache after updating
