=== WP-SlidesJS-Alt ===
Contributors: cadeyrn, mattheweppelsheimer
Tags: jQuery, slides, slide
Requires at least: 2.7
Tested up to: 3.3
Stable tag: 0.1
License: Apache License, Version 2.0

Adds a shortcut function to WordPress to create Slides JS slideshow from posts of a category.

== Description ==

/**
 *	@todo  
 *
 *	Working on for Version 0.4: 
		[ ] a default slide template for rendering that replaces display-slides.php
		[ ] mechanism for extending slide options in the backend per site, using filters
		[x] cleanup cruft
		[ ] completely genericized

 *	Targeted for 0.5: 
		[ ] Create custom template builder
		[ ] consider making templates object-oriented
 *
 *	Targeted for 0.6: 
		[ ] Multiple slideshow support in the backend
		[ ] call rli_wpslidesjs_frontend_setup() dynamically

 *
 *	Targeted for 0.7: 
		[ ] Code documentation, code cleanup
		- add consistent textdomain params to __() uses
		- around global #pagenow, pass $hook and do stuff based on that, rather than doing the crazy-specific action hook. 
		- in rli_wpslidesjs_save_meta(), an option to cleanup using delete_post_meta
		- Backend menu icons for slides and slideshows
		- Daniel's suggestsion within rli_wpslidesjs_save_meta is to (within global scope) setup arrays of string values to work on using foreach loops, rather than the repetetive isset() stuff. 
		- Review use of thickbox media uploader

 *	Targeted for 0.8: 
		- [Premium] Backend slideshow builder 
		- [Premium] Multiple slideshow support per page in the front-end
 */

NOTE THIS FILE REQUIRES UPDATING. IT REFLECTS THE ORIGINAL PLUGIN VERSION.

WP-SlidesJS adds `wp-slidesjs` shortcut function to WordPress in order to create [Slides JS](http://slidesjs.com/ "Slides JS") slideshow from posts of a category.

= Usage =
Place `[wp-slidesjs]` shortcode into anywhere in the post or a page. You can use the following to specify the posts to show:

* `category_id`: ID of the category to show posts from
	or
* `category_slug`: slug of the category to show posts from
	or
* `page_id`: id of the page to show
	or
* `post_id`: id of the post to show

	and (optional)
* `limit`: limit the number of the posts to show

Example 1:
`[wp-slidesjs category_id=12 limit=6]`

This will create the slider from the last 6 posts of category #12. You'll going to have 6 slides this way.

Example 2:
`[wp-slidesjs page_id=3,211,1389,1121 ]`

This will create the slider from pages #3, #211, #1389 and #1121. You'll going to have 4 slides this way.

The number of sliders per page is unlimited.

== Installation ==
1. Upload contents of `wp-slidesjs.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Fine tune the settings in `Settings` -> `wp-slidesjs` menu in WordPress

== Frequently Asked Questions ==

== Screenshots ==
1. With default CSS enabled

== Changelog ==

= 0.4.1 =
2012.01.21

* typo correction

= 0.4 =
2012.01.21

* added functionality of adding multiple ids separated by ','
* added support for `page_id` & `post_id` to select exact pages as source of slides

= 0.3 =
2012.01.05

* bugfixes, special thanks to Bardsart

= 0.2 =
2012.01.05

* DO NOT USE THIS VERSION

= 0.1 =
2011.12.27

* initial release
