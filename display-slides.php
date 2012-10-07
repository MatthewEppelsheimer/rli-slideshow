<?php

/*
 *	rli_wpslidesjs_display_slideshow() builds and echos the slideshow
 * 
 *	@param $slideshow: A CURRENTLY NON-FUNCTIONAL name of the slideshow
 *	@todo support multiple slideshows saved by name.
 */

function rli_wpslidesjs_display_slideshow( $slideshow ) {
	$slide_output ="";
	global $slide_styles;

	$slides = new WP_Query(
		array(
			'post_type' => 'rli_slide',
			'posts_per_page' => -1,
			'order' => 'ASC'
		)
	);

	if( $slides->have_posts() ) {

		$slide_styles = "\n<style type='text/css'>\n";

		while ( $slides->have_posts() ) {
	    	$slides->the_post();
			    	
			// retrieve existing values
			$primary_button_text = get_post_meta( $post->ID, '_rli_wpslidesjs_primary_button_text', true );
			$primary_link = get_post_meta( $post->ID, '_rli_wpslidesjs_primary_button_uri', true );
			$secondary_button_text = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_text', true );
			$secondary_link = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_uri', true );
			$secondary_color = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_color', true );
			$background_image = get_post_meta( $post->ID, '_rli_wpslidesjs_background_image', true );
																		    	
	    	$slide_output .= "
				<div class='ssi_slide' id='rli-slide-" . $post->ID ."'>\n
					<div>\n
						<h2><a href='$primary_link'>" . the_title( '', '', false ) ."</a></h2>\n
						" . apply_filters( 'the_content', get_the_content() ) . "\n
						<p class='multi-cta'><a href='$primary_link' class='primary cta'>$primary_button_text</a></p>\n";
						if ( $secondary_link != '' ) $slide_output .= "
							<p class='multi-cta'><a href='$secondary_link' class='primary cta-offset' style='background-color:$secondary_color'>$secondary_button_text</a></p>\n";
						$slide_output .= "
					</div>\n
				</div>\n";

			if ( $background_image != "" )
				$slide_styles .= "#rli-slide-" . $post->ID . " { background-image:url('" . $background_image . "'); }\n";
		}

		$slide_styles .= "</style>\n";

		add_action( 'wp_head', function() { global $slide_styles; echo $slide_styles; } );
	}
}
