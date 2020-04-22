/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	config.language = 'pl';
	config.enterMode = CKEDITOR.ENTER_BR;
	config.shiftEnterMode = CKEDITOR.ENTER_P;
	config.entities_latin = false;
	config.skin = 'kama';
	config.toolbar = "cms";
	config.disableNativeSpellChecker = false;
	config.removePlugins = 'scayt';
  config.allowedContent = true;

	config.extraPlugins = 'youtube,pbckcode,gg';
	
	config.pbckcode = {
	'cls'         : '',
	'modes'       : [ ['XML' , 'xml'], ['PHP'  , 'php'], ['HTML' , 'html'], ['CSS'  , 'css'], ['JS'   , 'javascript'] ],
	'defaultMode' : 'html',
	'theme' : 'chrome'
	};

	config.toolbar_cms =
	[
		['Source'],
		['Cut','Copy','Paste','PasteText'],
		['Replace','-','RemoveFormat'],
		['Bold','Italic','Underline','Strike'],
		['NumberedList','BulletedList'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['Link','Unlink'],
		['Image','Flash','Youtube','gg','Table','HorizontalRule','SpecialChar','pbckcode'],
		['Styles','Format','Font','FontSize', 'Subscript', 'Superscript'],
		['TextColor','BGColor']
	];
};
