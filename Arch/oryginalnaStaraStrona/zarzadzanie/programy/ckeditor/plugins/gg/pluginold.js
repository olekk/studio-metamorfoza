CKEDITOR.plugins.add( 'gg',
{
	init: function( editor )
	{
	editor.addCommand( 'ggDialog', new CKEDITOR.dialogCommand( 'ggDialog' ) );
if ( !CKEDITOR.config.GoogleMaps_Key || CKEDITOR.config.GoogleMaps_Key.length === 0){
editor.ui.addButton( 'gg',
{

	label: 'Gmap',
	command: 'ggDialog',
	icon: this.path + 'images/gg.png'
});
}


CKEDITOR.dialog.add( 'ggDialog', function( editor )
{
	return {
		title : 'google Map',
		minWidth : 400,
		minHeight : 200,
		contents :
		[
			{
				id : 'general',
				label : 'Settings',
				elements :
				[
				 	// UI elements of the Settings tab.
				]
			}
		]
	};
});



	}
});