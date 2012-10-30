/*
 * See http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/ 
 */

/* Still under development! */


jQuery(document).ready(function() {

	attachmentInput = jQuery('#rli_slide_background_image_path');
 
	jQuery('#rli-slide-choose-background').click(function() {
		// formfield = attachmentInput.attr('name');
		attachmentInput.val( tb_show('', 'media-upload.php?TB_iframe=true') );
		return true;
	});



	
	window.send_to_editor = function(html) {
		/*
		imgurl = jQuery('.urlfield').attr('value');
		if ( console ) console.log(imgurl);
		attachmentInput.val(imgurl);
		*/
		tb_remove();
	}
	
 
});