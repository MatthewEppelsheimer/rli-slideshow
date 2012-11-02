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
					if ( isset( $template_part['html'] ) && isset( $template_part['html_params'] ) ) {
						$output .= sprintf( $template_part['html'], $template_part['html_params'][0], the_content() );
					} else {
						$output .= the_content();
					}
					break;
			}
			break;

		case 'string':
			if ( isset( $template_part['html'] ) ) {
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
			$converted_param = ".rli-slide-" . $post->ID;
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

	if ( ! isset( $template_part['css'] ) || ! isset( $template_part['css_params'] ) )
		return $output;

	$css_constructor = array();
	foreach ( $template_part as $param ) {
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

function rli_slideshow_display_slideshow( $slideshow = 'default' ) {
	global $post;

	$slides = rli_slideshow_get_slides();

	if( $slides->have_posts() ) {

		// @todo Prepare default slideshow-wide CSS rules, such as default background rules.

		$slide_output = $slide_styles = "";

		// Setup script
		// @todo make this dynamic and option driven
		// @todo target the specific slideshow by ID
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

			// Setup output variables
			$slide_classes = $html = $css = "";

			// Load the slide's data
			$slide_settings = get_post_meta( $post->ID, '_rli_slideshow_slide_settings', true );

			// Get and store the template name.
			if ( ! isset( $slide_settings['template'] ) ) 
				$slide_settings['template'] = 'default';
			$slide_template = $slide_settings['template'];

			// Get the template
			$template_specs = rli_slideshow_get_slide_template_specifications( $slide_template );

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

			$slide_styles .= $css;

	    	$slide_output .= "
				<div class='rli-slide rli-slide-" . $post->ID ." $slide_classes' >
					$html
				</div>\n";
		}

		// echo styles
		echo "<style type='text/css'>\n$slide_styles</style>\n";

		// echo html
		echo "<div class='rli-slideshow' style='display:none;'>\n<div class='rli-slideshow-container'>\n$slide_output</div>\n</div>\n";

		// echo js
		echo $slide_script;
	}
}
