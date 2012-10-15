<?php

/*
 *	rli_wpslidesjs_display_slideshow() builds and echos the slideshow
 * 
 *	@param $slideshow: A CURRENTLY NON-FUNCTIONAL name of the slideshow
 *	@todo support multiple slideshows saved by name.
 *	@todo make $default_background_path a generic option.
 */

function rli_wpslidesjs_display_slideshow( $slideshow ) {
	global $post;

	$slides = new WP_Query(
		array(
			'post_type' => 'rli_slide',
			'posts_per_page' => -1,
			'order' => 'ASC'
		)
	);

	if( $slides->have_posts() ) {

		// @todo make this an option to be generic
		$default_background_path = "http://dev.rocketlift.com/happyvalleypc/wp-content/uploads/2012/10/slide-background.jpg";
		// @todo make this target a dynamic id
		$slide_styles = "\n<style type='text/css'>\ndiv#rli-slideshow div.default-background { background-image: url('$default_background_path');}\n";
		$slide_output = "<div id='rli-slideshow'>\n<div class='rli-slideshow-container'>\n";


		// Setup script
		// @todo make this dynamic and option driven
		// @todo move this out of the display loop; do this only once per page so it applies just once to every slideshow on the page
		// @todo make id targeting dynamie
		$slide_script = "
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('#rli-slideshow').slides({
						play: 7000,
						effect: 'fade',
						crossface: true,
						hoverPause: true,
						slideSpeed: 400,
						pagination: false,
						generatePagination: false,
						container: 'rli-slideshow-container',
						currentClass: 'rli-wpslidesjs-current',
						paginationClass: 'rli-wpslidesjs-pages',
					});
				});
			</script>";

		while ( $slides->have_posts() ) {
	    	$slides->the_post();
			    	
			// retrieve existing values
			$primary_button_text = get_post_meta( $post->ID, '_rli_wpslidesjs_primary_button_text', true );
			$primary_link = get_post_meta( $post->ID, '_rli_wpslidesjs_primary_button_uri', true );
			$secondary_button_text = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_text', true );
			$secondary_link = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_uri', true );
			$secondary_color = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_color', true );
			$background_image = get_post_meta( $post->ID, '_rli_wpslidesjs_background_image', true );
			$foreground_image_path = get_post_meta( $post->ID, '_rli_wpslidesjs_foreground_image', true );

			// Misc Setup
			$slide_classes = "";

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
