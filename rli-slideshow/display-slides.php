<?php
/**
 *	Render html for a slide template part
 *
 *	@param	str	$template_part
 *	@param	str	$data
 *
 *	@todo	support case where 'setting_type' is 'string'
 */

function rli_slideshow_render_slide_html_from_template( $template_part, $data ) {
	$output = '';
	switch ( $template_part['setting_type'] ) {

		case 'lookup':
			switch ( $template_part['lookup'] ) {
				case 'the_content':
					if ( isset( $template_part['html'] && isset( $template_part['html_params'] ) ) {
						$output .= sprintf( $template_part['html'], $template_part['html_params'][0], the_content() );
					} else {
						$output .= the_content();
					}
					break;
			}
			break;

		case 'string':
			if ( isset( $template_part['html'] ) {
				// @todo SUPPORTED IN FUTURE
			}
			break;
	}
	return $output;
}

/**
 *	rli_slideshow_slide_template_convert_css_params()
 *	
 *	@description	Callback to prepare CSS Params for use by rli_slideshow_render_slide_css_from_template
 *
 *	@param	str	$param	The type of param from the template specification
 *	@param	*	$data	The slide's setting value for the given slide template part
 *
 *	@return		CSS rule fragments (for later use with vsprintf)
 */

function rli_slideshow_slide_template_convert_css_params( $param, $data ) { 
	global $post;

	$converted_param = '';

	switch ( $param ) {
		case 'slide_id':
			$converted_param = "#rli-slide-" . $post->ID;
			break;
		case 'data':
			$converted_param = $data;
			break;
	}

	return $converted_param;
}


/**
 *	Render css for a slide template part
 *
 *	@uses	rli_slideshow_slide_template_convert_css_params()
 *
 *	@param	str	$template_part
 *	@param	str	$data
 */

function rli_slideshow_render_slide_css_from_template( $template_part, $data ) {
	global $post;

	$output = '';

	if ( ! isset( $template_part['css'] || ! isset( $template_part['css_params' ) )
		return $output;

	$css_constructor = array();
	foreach ( $template_part['css'] as $param ) {
		$css_constructor[] .= rli_slideshow_slide_template_convert_css_params( $param, $data );
	}

	$output .= vsprintf( $template_part['css'], $css_constructor );
	return $output;
}

/**
 *	Render slide class from a slide template part
 *
 *	@param	str	$template_part
 *	@param	str	$data
 *
 *	@return	str		class to add to slide wrapper
 */

function rli_slideshow_render_slide_class_from_template( $template_part, $data ) {
	$output = '';

	if ( ! isset( $template_part['slide_class'] ) )
		return $output;

	if ( $data == '' ) {
		$output .= $template_part['slide_class'][1];
	} else {
		$output .= $template_part['slide_class'][0];
	}

	$output .= ' '; // add single whitespace separator

	return $output;
}

/*
 *	rli_slideshow_display_slideshow() builds and echos the slideshow
 * 
 *	@param $slideshow: A CURRENTLY NON-FUNCTIONAL name of the slideshow
 *	@todo order slides
 *	@todo support multiple slideshows saved by name.
 *	@todo make $default_background_path a generic option.
 */

function rli_slideshow_display_slideshow( $slideshow ) {
	global $post;

	$slides = rli_slideshow_get_slides();

	if( $slides->have_posts() ) {

		// @todo make this target a dynamic id
		// @todo re-enable this after default background image is a setting
		/*
		$slide_styles = "\n<style type='text/css'>\ndiv#rli-slideshow div.default-background { background-image: url('$default_background_path');}\n";
		*/
		$slide_output = "<div class='rli-slideshow' style='display:none;'>\n<div class='rli-slideshow-container'>\n";

		// Setup script
		// @todo make this dynamic and option driven
		// @todo move this out of the display loop; do this only once per page so it applies just once to every slideshow on the page
		// @todo make id targeting dynamie
		$slide_script = "
			\n<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('#rli-slideshow').css('display','block').slides({
						play: 7000,
						effect: 'fade',
						crossface: true,
						hoverPause: true,
						slideSpeed: 400,
						pagination: false,
						generatePagination: false,
						container: 'rli-slideshow-container',
						currentClass: 'rli-wpslidesjs-current',
						paginationClass: 'rli-wpslidesjs-pages'
					});
				});
			</script>\n";

		while ( $slides->have_posts() ) {
	    	$slides->the_post();

			// Load the slide's data
			$slide_settings = get_post_meta( $post->ID, '_rli_slideshow_slide_settings', true );

			// Get and store the template name.
			if ( ! isset( $slide_settings['template'] ) ) 
				$slide_settings['template'] = 'default';
			$slide_template = $slide_settings['template'];

			// Get the template
			$template_specs = rli_slideshow_get_slide_template_specifications( $slide_template );

			// Setup output variables
			$slide_classes = $html = $css = "";

			// @todo ORDER SLIDES

			// Build the slide content, styles, and classes based on the template and slide data
			foreach ( $template_specs as $setting ) {
				$slug = $setting['slug'];
				if ( ! isset( $slide_settings[$slug] ) )
					$slide_settings[$slug] = '';
				$html .= rli_slideshow_render_slide_html_from_template( $setting, $slide_settings[$slug] );
				$css .= rli_slideshow_render_slide_css_from_template( $setting, $slide_settings[$slug] );
				$slide_classes .= rli_slideshow_render_slide_class_from_template( $setting, $slide_settings[$slug] );
			}



		/*				OLD CODE			*/

			// Setup background
			if ( $background_image != "" ) {
				$slide_styles .= "#rli-slide-" . $post->ID . " { background-image:url('" . $background_image . "'); }\n";
			} else {
				$slide_classes .= "default-background ";
			}

			// Setup foreground
			$foreground = '';
			if ( $foreground_image_path != "" ) 
				$foreground = "<img src='$foreground_image_path' />\n";

			// Setup for title
			// This will later be implemented as an option (and/or as a filter)
			$title_element = 'h2';
			if ( $primary_link != '' ) {
				$title = "<$title_element><a href='$primary_link'>" . the_title( '', '', false ) ."</a></$title_element>\n";
			} else {
				$title = "<$title_element>" . the_title( '', '', false ) ."</$title_element>\n";
			}

			if ( ! $include_title ) 
				$title = '';

			// Setup for links
			$first_link = $second_link = "";
			if ( $primary_link != '' ) 
				$first_link = "<p class='first-link'><a href='$primary_link' class='primary'>$primary_button_text</a></p>\n";
			if ( $secondary_link != '' ) 
				$second_link = "<p class='second-cta'><a href='$secondary_link' class='secondary'>$secondary_button_text</a></p>\n";

			// Setup special message
			$common_message = "<p class='rli-slide-common'>Call us at <strong>(503) 482-9011</strong></p>\n";

	    	$slide_output .= "
				<div class='rli-slide $slide_classes' id='rli-slide-" . $post->ID ."'>\n";
			if ( $foreground != '' ) $slide_output .=
					"<div class='rli-slide-foreground'>\n
						$foreground
					</div>";
			$slide_output .=
					"<div class='rli-slide-content'>\n
						$title
						" . apply_filters( 'the_content', get_the_content() ) . "\n
						$first_link
						$second_link
					</div>\n";
			if ( $common_message != '' ) $slide_output .=
					"$common_message";
			$slide_output .=
				"</div>\n";

		}

		// echo styles
		$slide_styles .= "</style>\n";
		echo $slide_styles;

		// echo html
		$slide_output .= "</div>\n</div>\n";
		echo $slide_output;

		// echo js
		echo $slide_script;
	}
}
