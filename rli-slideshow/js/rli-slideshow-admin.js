/*
 * See http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/ 
 */

/* Still under development! */

// "Struct" containing all the info for the custom media uploader
var upload_media = {
	'textfield': '',
	'img_id': '',
	'img_url': '',
	'default_send_to_editor': '',
	'new_send_to_editor':''
};


jQuery(document).ready(function() {
 	upload_media.default_send_to_editor = window.send_to_editor; // save default send to editor
	upload_media.new_send_to_editor = function(html) {
		upload_media.img_url = jQuery('img',html).attr('src');
		jQuery('#' + upload_media.textfield.id).val(upload_media.img_url);
		
		tb_remove();
		window.send_to_editor = upload_media.default_send_to_editor; // reset send to editor to default
		jQuery('#' + upload_media.img_id.id + " img").attr('src',upload_media.img_url);
	}
	
});

function rli_upload_media(textfield_id, image_id){
	upload_media.textfield = textfield_id;
	upload_media.img_id = image_id;
	window.send_to_editor = upload_media.new_send_to_editor;
	formfield = jQuery('text').attr('id');
	tb_show('Upload Slide', 'media-upload.php?referer=rli_slide_select_settings&type=image&TB_iframe=true&post_id=0', false);
	return false;
} 

