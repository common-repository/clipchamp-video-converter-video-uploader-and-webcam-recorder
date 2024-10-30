var ccbUploadVideo = function (blob, done, fail, notify) {
	var data = new FormData();
	data.append( 'action', 'ccb_upload' );
	if ( ! blob.name) {
		var timestamp = new Date().getTime();
		var ext = blob.type.split( '/' ).pop();
		if (ext) {
			ext = ext.split( ';' ).shift();
			data.append( 'video', blob, 'clipchamp_' + timestamp + '.' + ext );
		} else {
			data.append( 'video', blob );
		}
	} else {
		data.append( 'video', blob );
	}

	jQuery.ajax(
		{
			xhr: function () {
				var xhr = new window.XMLHttpRequest();

				xhr.upload.addEventListener(
					"progress", function (evt) {
						if (evt.lengthComputable) {
							var percentComplete = evt.loaded / evt.total;
							notify( percentComplete );
						}
					}, false
				);

				return xhr;
			},
			url: ccb_ajax.ajax_url,
			type: 'POST',
			data: data,
			contentType: false,
			processData: false
		}
	)
		.done(
			function (response) {
				done( response );
			}
		)
		.fail(
			function () {
				fail();
			}
		);
};

var $ccbMetadata = jQuery.Deferred();
var ccbMetadataAvailable = function (metadata) {
	$ccbMetadata.resolve( metadata );
};

var $ccbPreviewImage = jQuery.Deferred();
var ccbPreviewAvailable = function (image) {
	$ccbPreviewImage.resolve( image );
};

var ccbUploadComplete = function (videoData) {
	function ensureFilename(name, mimeType) {
		var type = mimeType.split( '/' )[1];
		return name + '.' + type;
	}

	if (typeof( ccbBeforeCreateHook ) === 'function') {
		videoData = ccbBeforeCreateHook( videoData ) || videoData;
	}
	var data = {
		action: 'ccb_upload_complete',
		data: videoData,
		_wpnonce: ccb_ajax.upload_complete_nonce
	};
	jQuery.post(
		ccb_ajax.ajax_url, data, function (postId) {
			var timeout = setTimeout(
				function () {
					$ccbPreviewImage.resolve();
				}, 1000
			);
			var gForm = jQuery( '.gform_wrapper' );
			if ( 0 !== gForm.length ) {
				gForm.find( 'input.clipchamp-button-input' ).val( postId );
				gForm.find( 'p.clipchamp-button-complete' ).show();
				gForm.find( 'p.clipchamp-button-complete input' ).val( videoData.filename );
			}
			$ccbPreviewImage.done(
				function (image) {
					clearTimeout( timeout );
					if (image) {
						var formData = new FormData();
						formData.append( 'action', 'ccb_upload_image' );
						formData.append( 'image', image, ensureFilename( 'video-' + postId + '-' + Date.now(), image.type ) );
						formData.append( 'post_id', postId );
						formData.append( '_wpnonce', ccb_ajax.upload_image_nonce );

						jQuery.ajax(
							{
								url: ccb_ajax.ajax_url,
								type: 'POST',
								data: formData,
								contentType: false,
								processData: false
							}
						)
						.done(
							function (response) {
								if (typeof( ccbAfterCreateHook ) === 'function') {
									ccbAfterCreateHook( postId, videoData, image );
								}
							}
						)
						.fail(
							function () {
								console.error( 'An error occured.' );
							}
						);
					} else {
						if (typeof( ccbAfterCreateHook ) === 'function') {
							ccbAfterCreateHook( postId, videoData );
						}
					}
				}
			);
		}
	);
};
