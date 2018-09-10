jQuery( document ).ready( function() {

	if( jQuery( '.sv_core_settings_uploader' ).length > 0 ) {
		var options = false;
		var container = jQuery( '.sv_core_settings_uploader' );
		options = JSON.parse( JSON.stringify( global_uploader_options ) );
		options['multipart_params']['_ajax_nonce'] = container.find( '.ajaxnonce' ).attr( 'id' );

		if( container.hasClass( 'multiple' ) ) {
			options['multi_selection'] = true;
		}

		var uploader = new plupload.Uploader( options );
		uploader.init();

		// EVENTS
		// init
		uploader.bind( 'Init', function( up ) {
			console.log( 'Init', up );
		} );

		// file added
		uploader.bind( 'FilesAdded', function( up, files ) {
			jQuery.each( files, function( i, file ) {
				console.log( 'File Added', i, file );
			} );

			up.refresh();
			up.start();
		} );

		// upload progress
		uploader.bind( 'UploadProgress', function( up, file ) {
			console.log( 'Progress', up, file )
		} );

		// file uploaded
		uploader.bind( 'FileUploaded', function( up, file, response ) {
			response = jQuery.parseJSON( response.response );

			if( response['status'] == 'success' ) {
				console.log( 'Success', up, file, response );
			} else {
				console.log( 'Error', up, file, response );
			}

		} );
	}

} );