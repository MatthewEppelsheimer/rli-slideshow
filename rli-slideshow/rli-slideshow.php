<?php
/*
Plugin Name: Rocket Lift Slideshow
Version: 0.4
Plugin URI: http://rocketlift.com/software/rli-slideshow
Description: Creates slideshow from 'slide' custom post type with Slides JS ( http://slidesjs.com/ ). NOTE this is pre-release alpha software geared specifically for The Mobile Tech PC website. It is not yet ready for open-source release.
Author: Matthew Eppelsheimer based on work by Peter Molnar
Author URI: http://rocketlift.com/
License: Apache License, Version 2.0
*/

/*  Copyright 2012 Rocket Lift Incorporated  (email : hello@rocketlift.com )
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
*/

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

/*
 * ACTIVATION
 *
 */

// set up 'rli_slide' custom post type.
// 
// TODO 'menu_icon' => 'some-image.png',

function rli_wpslides_create_rli_slide_post_type() {
	register_post_type( 'rli_slide',
					array(
						'labels' => array(
							'name' => __( 'Slides' ),
							'singular_name' => __( 'Slide' )
						),
						'public' => true,
						'description' => 'Individual slides to be included in a slide deck.',
						'exclude_from_search' => true,
						'publicly_queryable' => false,
						'show_in_nav_menus' => false,
						'menu_position' => 5,
						'map_meta_cap' => true,
						'hierarchical' => true,
						'supports' => array(
							'title' , 'editor' , 'thumbnail' , 'page-attributes'
						),
						'has_archive' => false
					)
				);
			}

add_action( 'init', 'rli_wpslides_create_rli_slide_post_type' );

// First, we "add" the custom post type via the above written function.
// Then we flush_rewrite_rules to set up permalinks.
// @todo Bachuber said this is too late to flush rewrite rules. What's the fix?
//

function rli_wpslidesjs_rewrite_flush()  {
    rli_wpslides_create_rli_slide_post_type();

    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'rli_wpslidesjs_rewrite_flush' );

/*
 * Set up UI assets for File Attachment Uploader
 * @todo how are we using this?
 */

function rli_wpslides_admin_scripts() {
	wp_register_script('rli-wpslidesjs-admin', plugins_url('js/rli-wpslidesjs-admin.js', __FILE__ ), array('jquery','media-upload','thickbox'));
	wp_enqueue_script('rli-wpslidesjs-admin');
}

function rli_wpslides_admin_styles() {
	wp_enqueue_style('thickbox');
}

function rli_wpslides_admin_assets() {
    global $post_type;
    if( 'rli_slide' == $post_type ) {
	    rli_wpslides_admin_scripts();
	    rli_wpslides_admin_styles();
    }
}

add_action( 'admin_print_scripts-post-new.php', 'rli_wpslides_admin_assets', 11 );
add_action( 'admin_print_scripts-post.php', 'rli_wpslides_admin_assets', 11 );


/*
 * rli_slideshow_slide_editor_metabox_render( $post, $template )
 * Builds the metabox for a slide based on its template
 * 
 * @param int $post - the global $post object
 * @param str $template - the slug name of the slide's template
 */

function rli_slideshow_slide_editor_metabox_render( $post, $template = 'default' ) {
	$output = "<div>\n";
	$template_options = rli_slideshow_get_slide_template_options( $post, $template );
}

/*
 * rli_slideshow_get_slide_template_options( $template )
 * Returns option keys and values associated with the given slideshow template
 *
 * @param int $post - the global $post object
 * @param str $template - the slug name of the slide's template
 */

function rli_slideshow_get_slide_template_options( $post, $template ) {
	// @todo replace this with a function to return $template_keys by looking up the template
	$template_keys = array(
		array(
			'slug' => 'background_image',
			'order' => 0,
			'name' => 'Background Image',
			'description' => 'The slide\'s background image',
			'help' => 'Defaults to the default image in settings'
			'css' => '%s { background-image: %s; }'
		),
		array(
			'slug' => 'content',
			'order' => 1,
			'description' => 'The slide\'s content, based on the editor box',
			'lookup' => 'the_content',
			'html' => '<div class=\'%s\'>%s</div>'
		),
		array(
			'slug' => 'link_text',
			'parameter' => true,
			'description' => 'The slide\'s content, based on the editor box',
			'lookup' => 'the_content',
			'html' => '<div class=\'%s\'>%s</div>'
		)
	);
	$slide_options = get_post_meta( $post->ID, '_rli_slideshow_options', true );
	// Where does the template come in?
}

/*
 * Generate html for Slide Settings Metabox
 */

function rli_wpslidesjs_settings_metabox_render( $post ) {

	// retrieve existing values
	$rli_wpslidesjs_primary_button_text = get_post_meta( $post->ID, '_rli_wpslidesjs_primary_button_text', true );
	$rli_wpslidesjs_primary_button_uri = get_post_meta( $post->ID, '_rli_wpslidesjs_primary_button_uri', true );
	$rli_wpslidesjs_secondary_button_text = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_text', true );
	$rli_wpslidesjs_secondary_button_uri = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_uri', true );
	$rli_wpslidesjs_secondary_button_color = get_post_meta( $post->ID, '_rli_wpslidesjs_secondary_button_color', true );
	$rli_wpslidesjs_background_image = get_post_meta( $post->ID, '_rli_wpslidesjs_background_image', true );
	$rli_wpslidesjs_foreground_image = get_post_meta( $post->ID, '_rli_wpslidesjs_foreground_image', true );
	$rli_wpslidesjs_slide_header_toggle = get_post_meta( $post->ID, '_rli_wpslidesjs_slide_header_toggle', true );

	// render  inputs
	echo "
		<div>
			<h4>Include Title?</h4>
			<p><input type='checkbox' name='rli_wpslidesjs_slide_header_toggle' ";
			if ( $rli_wpslidesjs_slide_header_toggle )
				echo "checked='checked' ";
			echo "value='yes' /></p>
			<h4>Primary Button</h4>
			<p>Text: <input type='text' name='rli_wpslidesjs_primary_button_text' value='" . esc_attr( $rli_wpslidesjs_primary_button_text ) . "' /> <em class='how-to'>Defaults to &ldquo;Learn More&rdquo;.</em></p>
			<p>Link address: <input type='text' name='rli_wpslidesjs_primary_button_uri' value='" . esc_attr( $rli_wpslidesjs_primary_button_uri ) . "' /> <strong><em class='how-to'>Required.</em></strong></p>
			
			<h4>Secondary Button</h4>
			<p>This is optional, and will only be displayed if both the text and link fields are filled out.</p>
			<p>Text: <input type='text' name='rli_wpslidesjs_secondary_button_text' value='" . esc_attr( $rli_wpslidesjs_secondary_button_text ) . "' /></p>
			<p>Link address: <input type='text' name='rli_wpslidesjs_secondary_button_uri' value='" . esc_attr( $rli_wpslidesjs_secondary_button_uri ) . "' /></p>
			<p>Background color: <input type='text' name='rli_wpslidesjs_secondary_button_color' value='" . esc_attr( $rli_wpslidesjs_secondary_button_color ) . "' /> <em class='how-to'>Must be in hexadecimal format including `#`. Defaults to &ldquo;#7b68ee&rdquo;.</em></p>

			<h4>Background Image</h4>
			<p><strong>Image must be 330 pixels high.</strong> The recommended width is 500 pixels.</p>
			<p>To select a background image:</p>
			<ol>
				<li>Click this button to upload or browse to an already uploaded file: <a class='button-secondary' id='rli-slide-choose-background'>Media Library</a></li>
				<li>Select and copy the <strong>Link URL</strong> field of the PDF.</li>
				<li>Close the Media Uploader screen.</li>
				<li>Paste the issue's <strong>Link URL</strong> from the Media Upload screen in this box: <input style='background-color:#ddd;width:400px;' id='rli_slide_background_image_path' type='text' name='rli_wpslidesjs_background_image' value='" . esc_attr( $rli_wpslidesjs_background_image ) . "' /></li>
			</ol>
			<h4>Foreground Image</h4>
			<p><input style='background-color:#ddd;width:400px;' id='rli_slide_foreground_image_path' type='text' name='rli_wpslidesjs_foreground_image' value='" . esc_attr( $rli_wpslidesjs_foreground_image ) . "' /></p>
		</div>";  

}

/*
 * Create Settings Metabox
 */

function rli_wpslidesjs_create_detail_metabox() {
	add_meta_box( 'rli-slide-settings', 'Slide Settings', 'rli_wpslidesjs_settings_metabox_render', 'rli_slide', 'normal', 'high' );
}

add_action( 'add_meta_boxes', 'rli_wpslidesjs_create_detail_metabox' );


// save metabox data

function rli_wpslidesjs_save_meta( $post_id ) {

	// include slide title toggle
	if ( isset( $_POST['rli_wpslidesjs_slide_header_toggle'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_slide_header_toggle', strip_tags( $_POST['rli_wpslidesjs_slide_header_toggle'] ) );
	} else { // default
		update_post_meta( $post_id, '_rli_wpslidesjs_slide_header_toggle', false ); 
	}

	// primary button text
	if ( isset( $_POST['rli_wpslidesjs_primary_button_text'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_primary_button_text', strip_tags( $_POST['rli_wpslidesjs_primary_button_text'] ) );
	} else { // default
		update_post_meta( $post_id, '_rli_wpslidesjs_primary_button_text', 'Learn More' );
	}

	// primary button uri
	if ( isset( $_POST['rli_wpslidesjs_primary_button_uri'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_primary_button_uri', strip_tags( $_POST['rli_wpslidesjs_primary_button_uri'] ) );
	}

	// secondary button text
	if ( isset( $_POST['rli_wpslidesjs_secondary_button_text'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_secondary_button_text', strip_tags( $_POST['rli_wpslidesjs_secondary_button_text'] ) );
	}

	// secondary button uri
	if ( isset( $_POST['rli_wpslidesjs_secondary_button_uri'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_secondary_button_uri', strip_tags( $_POST['rli_wpslidesjs_secondary_button_uri'] ) );
	}

	// secondary button color
	if ( isset( $_POST['rli_wpslidesjs_secondary_button_color'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_secondary_button_color', strip_tags( $_POST['rli_wpslidesjs_secondary_button_color'] ) );
	} else { // default
		update_post_meta( $post_id, '_rli_wpslidesjs_secondary_button_color', '#7b68ee' );
	}

	// Background image
	if ( isset( $_POST['rli_wpslidesjs_background_image'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_background_image', strip_tags( $_POST['rli_wpslidesjs_background_image'] ) );
	}

	// Foreground image
	if ( isset( $_POST['rli_wpslidesjs_foreground_image'] ) ) {
		update_post_meta( $post_id, '_rli_wpslidesjs_foreground_image', strip_tags( $_POST['rli_wpslidesjs_foreground_image'] ) );
	}

}

add_action( 'save_post', 'rli_wpslidesjs_save_meta' );

/**
 * Modal Button.
 *
 * Create a button in the modal media window to associate the current image with the slide.
 *
 * @param     array     Multidimensional array representing the images form.
 * @param     stdClass  WordPress post object.
 * @return    array     The image's form array with added button if modal window was accessed by this script.
 *
 * @access    private
 * @since     2010-10-28
 * @alter     0.7
 */
function taxonomy_image_plugin_modal_button( $fields, $post ) {
	if ( isset( $fields['image-size'] ) && isset( $post->ID ) ) {
		$image_id = (int) $post->ID;

		$o = '<div class="taxonomy-image-modal-control" id="' . esc_attr( 'taxonomy-image-modal-control-' . $image_id ) . '">';

		$o.= '<span class="button create-association">' . sprintf( esc_html__( 'Associate with %1$s', 'taxonomy-images' ), '<span class="term-name">' . esc_html__( 'this term', 'taxonomy-images' ) . '</span>' ) . '</span>';

		$o.= '<span class="remove-association">' . sprintf( esc_html__( 'Remove association with %1$s', 'taxonomy-images' ), '<span class="term-name">' . esc_html__( 'this term', 'taxonomy-images' ) . '</span>' ) . '</span>';

		$o.= '<input class="taxonomy-image-button-image-id" name="' . esc_attr( 'taxonomy-image-button-image-id-' . $image_id ) . '" type="hidden" value="' . esc_attr( $image_id ) . '" />';

		$o.= '<input class="taxonomy-image-button-nonce-create" name="' . esc_attr( 'taxonomy-image-button-nonce-create-' . $image_id ) . '" type="hidden" value="' . esc_attr( wp_create_nonce( 'taxonomy-image-plugin-create-association' ) ) . '" />';

		$o.= '<input class="taxonomy-image-button-nonce-remove" name="' . esc_attr( 'taxonomy-image-button-nonce-remove-' . $image_id ) . '" type="hidden" value="' . esc_attr( wp_create_nonce( 'taxonomy-image-plugin-remove-association' ) ) . '" />';

		$o.= '</div>';

		$fields['image-size']['extra_rows']['taxonomy-image-plugin-button']['html'] = $o; }
	return $fields;
}

add_filter( 'attachment_fields_to_edit', 'taxonomy_image_plugin_modal_button', 20, 2 );

/*
 *	rli_wpslidesjs_frontend_setup() to enqueue JS
 *	Currently, this must be called manually in a theme template file 
 *	before the 'wp_head' action to avoid including it everywhere.
 *
 *	@todo Rethink this.
 */

function rli_wpslidesjs_frontend_setup() {
	wp_enqueue_script( 'rli-jquery-slides' , plugins_url( 'js/slides.min.jquery.js', __FILE__ ) , array('jquery') );
}

/*
 *	Pull in assets for slidshow display
 */

require_once( plugins_url( 'display-slides.php', __FILE__ );

// Support for direct manipulation with action hooks in theme templates
add_action( 'rli_wpslides', 'rli_wpslidesjs_display_slideshow' );

/*
 *	rli_wpslidesjs_register_shortcode() registers shortcode 
 */

function rli_wpslidesjs_register_shortcode() {
	add_shortcode( 'rli-slidshow', 'rli_slideshow_shortcode' );
}

add_action( 'init', 'rli_wpslidesjs_register_shortcode' );

/*
 * rli_slideshow_shortcode() creates a shortcode to display a slideshow on demand
 */

function rli_slideshow_shortcode( $atts ) {
	extract( 
		shortcode_atts( 
			array(
				'slideshow' => 'default'
			), 
			$atts 
		) 
	);

	return rli_wpslidesjs_display_slideshow( $slideshow );
}
