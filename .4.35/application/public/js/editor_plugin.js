(function() {
	tinymce.create('tinymce.plugins.ExamplePlugin', {
		init : function(ed, url) {
			url = url.substr(0, url.length - 3);
			ed.addCommand('mceicharts', function() {
				if (ed.selection.getContent() == "") {
					ed.windowManager.open( {
						file : icharts.data.popup_url,
						width : 270,
						height : 75,
						inline : 1
					}, {
						plugin_url : url
					});
				} else {
					ed.selection.setContent('[ichart url="' + ed.selection
							.getContent() + '"]')
				}
			});
			ed.addButton('icharts', {
				title : 'iCharts',
				cmd : 'mceicharts',
				'image' : url + '/images/tinymce.jpg'
			});
		}
	});
	tinymce.PluginManager.add('icharts', tinymce.plugins.ExamplePlugin);
})();