/*
 * See http://www.webmaster-source.com/2010/01/08/using-the-wordpress-uploader-in-your-plugin-or-theme/ 
 */

/* Still under development! */

var attachmentInput = '';

jQuery(document).ready(function() {
 
	jQuery('#rli-slide-choose-background').click(function() {
		attachmentInput = jQuery(this).prev('input'); /*grab the specific input*/
		formfield = jQuery('text').attr('id');
		tb_show('Upload Slide', 'media-upload.php?type=image&TB_iframe=true&post_id=0', false);
		return false;
	});

});

window.send_to_editor = function(html) {
	
	var img_url = jQuery('img',html).attr('src');
	//if ( console ) console.log(img_url);
	//alert(html);	
	//alert(img_url);
	jQuery('#rli-slideshow-background-image').val(img_url);
	
	//attachmentInput.val(img_url);
	
	tb_remove();
}

/*
window.send_to_editor = function(html) {
	
	imgurl = jQuery('.urlfield').attr('value');
	if ( console ) console.log(imgurl);
	attachmentInput.val(imgurl);
	
	tb_remove();
}
*/
