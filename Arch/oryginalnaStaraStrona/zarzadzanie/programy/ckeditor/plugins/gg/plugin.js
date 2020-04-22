// Register a new CKEditor plugin.
// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.resourceManager.html#add
CKEDITOR.plugins.add( 'gg',
{
	// The plugin initialization logic goes inside this method.
	// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.pluginDefinition.html#init
	init: function( editor )
	{
		// Create an editor command that stores the dialog initialization command.
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.command.html
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialogCommand.html
		editor.addCommand( 'simpleLinkDialog', new CKEDITOR.dialogCommand( 'simpleLinkDialog' ) );
 
		// Create a toolbar button that executes the plugin command defined above.
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.html#addButton
		editor.ui.addButton( 'gg',
		{
			// Toolbar button tooltip.
			label: 'Insert a ZS Google map',
			// Reference to the plugin command name.
			command: 'simpleLinkDialog',
			// Button's icon file path.
			icon: this.path + 'images/gg.png'
		} );
 
		// Add a new dialog window definition containing all UI elements and listeners.
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.html#.add
		// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.dialogDefinition.html
		CKEDITOR.dialog.add( 'simpleLinkDialog', function( editor )
		{
			return {
				// Basic properties of the dialog window: title, minimum size.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.dialogDefinition.html
				title : 'ZmajSoft Google Map',
				minWidth : 400,
				minHeight : 200,
				// Dialog window contents.
				// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.definition.content.html
				contents :
				[
					{
						// Definition of the Settings dialog window tab (page) with its id, label and contents.
						// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.contentDefinition.html
						id : 'general',
						label : 'Settings',
						elements :
						[
							// Dialog window UI element: HTML code field.
							// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.html.html
							{
								type : 'html',
								// HTML code to be shown inside the field.
								// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.html.html#constructor
								html : 'Wprowdź lokalizację. </br> kliknij <a href="http://www.mapcoordinates.net/en" target="_blank"> -->TUTAJ<--</a> żeby znaleźć współrzędne.'
							},
							// Dialog window UI element: a text input field for the Latitude.
							// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.textInput.html
							{
								type : 'text',
								id : 'lat',
								label : 'Latitude',
								validate : CKEDITOR.dialog.validate.notEmpty( 'Wpisz LATITUDE.' ),
								required : true,
								commit : function( data )
								{
									data.lat = this.getValue();
								}
							},
                   // Again same thing, this time for Longitude
						{
								type : 'text',
								id : 'lon',
								label : 'Longitude ',
								validate : CKEDITOR.dialog.validate.notEmpty( 'Wpisz LONGITUDE.' ),
								required : true,
								commit : function( data )
								{
									data.lon = this.getValue();
								}
							},
		                 // Dialog window UI element: HTML code field.
							// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.html.html
							{
								type : 'html',
								// HTML code to be shown inside the field.
								// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.ui.dialog.html.html#constructor
								html : 'Plugin by zmajsoft@zmajsoft.com'
							}
						]
					}
				],
				onOk : function()
				{
					// Create a link element and an object that will store the data entered in the dialog window.
					// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dom.document.html#createElement
					var dialog = this,
						data = {},
						link = editor.document.createElement( 'a' );
					// Populate the data object with data entered in the dialog window.
					// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dialog.html#commitContent
					this.commitContent( data );
    
					// Set the URL (href attribute) of the link element.
					// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dom.element.html#setAttribute
				//	link.setAttribute( 'href', data.url );





					// Insert the Displayed Text entered in the dialog window into the link element.
					// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.dom.element.html#setHtml
				//	link.setHtml( data.url);

					// Insert the link element into the current cursor position in the editor.
					// http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.editor.html#insertElement
					editor.insertHtml('<img src="http://maps.googleapis.com/maps/api/staticmap?size=512x512&maptype=roadmap&markers=color:red%7Ccolor:red%7Clabel:C%7C'+data.lat+','+data.lon+'&sensor=false"/>');
				}
			};
		} );
	}
} );