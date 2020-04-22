/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.plugins.add( 'docprops', {
	requires: 'wysiwygarea,dialog',
	lang: 'de,en,pl', // %REMOVE_LINE_CORE%
	icons: 'docprops,docprops-rtl', // %REMOVE_LINE_CORE%
	hidpi: true, // %REMOVE_LINE_CORE%
	init: function( editor ) {
		var cmd = new CKEDITOR.dialogCommand( 'docProps' );
		// Only applicable on full page mode.
		cmd.modes = { wysiwyg: editor.config.fullPage };
		cmd.allowedContent = {
			body: {
				styles: '*',
				attributes: 'dir'
			},
			html: {
				attributes: 'lang,xml:lang'
			}
		};
		cmd.requiredContent = 'body';

		editor.addCommand( 'docProps', cmd );
		CKEDITOR.dialog.add( 'docProps', this.path + 'dialogs/docprops.js' );

		editor.ui.addButton && editor.ui.addButton( 'DocProps', {
			label: editor.lang.docprops.label,
			command: 'docProps',
			toolbar: 'document,30'
		});
	}
});
