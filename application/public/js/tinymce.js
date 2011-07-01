function icharts_tinymce() {
};
jQuery(document).ready(function() {
	icharts_tinymce = new icharts_tinymce();
	tinyMCEPopup.executeOnLoad('icharts_tinymce.init();');
});
icharts_tinymce.prototype.init = function() {
	jQuery('#cancel').click(this.cancel);
	jQuery('#insert').click(this.insert);
	jQuery('[name="id"]').change(this.id_change);
}
icharts_tinymce.prototype.cancel = function(e) {
	e.preventDefault();
	tinyMCEPopup.close();
}
icharts_tinymce.prototype.insert = function(e) {
	e.preventDefault();
	if (window.tinyMCE) {
		var url=jQuery('[name="url"]').val();
		var text = '[ichart url="'+url+'"]';
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent',
				false, text);
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	tinyMCEPopup.close();
}