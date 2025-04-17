=== Maintenance Mode Made Easy ===
Contributors: polyplugins
Tags: maintenance, maintenace mode, maintenance page, under construction, woocommerce
Tested up to: 6.7
Stable tag: 1.0.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A lightweight plugin that makes managing site downtime easy. Send 503 headers, track with Google Analytics, prevent WooCommerce orders, and more.


== Description ==

Easily manage your site’s maintenance mode with Maintenance Mode Made Easy, a lightweight and user-friendly WordPress plugin. Designed to give you full control while keeping things simple, this plugin ensures your site’s downtime is professional and hassle-free.


## Features

* Enable or disable maintenance mode effortlessly from the WordPress admin bar, no complicated settings required.
* Automatically sends a 503 HTTP status header to inform search engines your site is temporarily unavailable, preserving your SEO rankings.
* Personalize the heading, message, color, background color, background color opacity (can also overlay background images), and background image displayed on the maintenance page to match your site’s tone and brand.
* Track maintenance page visits seamlessly with built-in Google Analytics & Matomo integration, which also checks for the [Complianz](https://wordpress.org/plugins/complianz-gdpr/) "cmplz_statistics" cookie before tracking.
* Blocks new WooCommerce orders during maintenance mode to prevent additional orders from being processed via AJAX.
* Build your own from the ground up maintenance page by creating a new maintenance.php in your active theme folder and selecting the "Custom" template in the settings.
* If Privacy Policy is defined in /wp-admin/options-privacy.php it will allow users to access this page when the site is in maintenance mode.


## Roadmap

* Add contact email and phone icons
* Add social media icons
* Start / End Date & Time
* WYSIWYG editor
* Design and build additional templates
* Redesign of settings page


## GDPR

We are not lawyers and always recommend doing your own compliance research into third party plugins, libraries, ect, as we've seen other maintenance plugins not be in compliance with these regulations.

This plugin uses Bootstrap & Font Awesome 3rd party libraries. These libraries are loaded locally to be compliant with data protection regulations.

This plugin has the capability to use Google Analytics and Matomo. If you enable this option, please make sure you are in compliance with data processing regulations. We currently have checks for the [Complianz](https://wordpress.org/plugins/complianz-gdpr/) "cmplz_statistics" cookie before tracking, but if you do not use Complianz you will need to build your own integration out. You can also put in a [feature request](https://www.polyplugins.com/contact/) for the compliance plugin you use, and we'll look into integrating it.

This plugin collects and stores certain data on your server to ensure proper functionality. This includes:

* Storing plugin settings
* Remembering which notices have been dismissed


## Assets

[Under Construction Keyboard](https://www.pexels.com/photo/under-construction-signage-on-laptop-keyboard-211122/)


== Installation ==

1. Backup WordPress
1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Click the Maintenance Mode item in the admin bar to configure the plugin


== Screenshots ==

1. Maintenance Mode Preview
2. General Settings
3. Design Settings
4. Analytic Settings


== Changelog ==

= 1.0.1 =
* Bugfix: Admins not able to bypass maintenance order restrictions when performing test orders

= 1.0.0 =
* Initial Release

