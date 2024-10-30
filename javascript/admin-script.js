/* global wp */

/**
 * Wrapper function to safely use $
 */
function ccbWrapper( $ ) {
	var ccb = {

		defaultFramerate: 24,

		/**
		 * Main entry point
		 */
		init: function () {
			ccb.prefix      = 'ccb_';
			ccb.templateURL = $( '#template-url' ).val();
			ccb.mediaUploader = null;

			// Init color picker
			$( '.color-field' ).wpColorPicker();

			// Init Codemirror
			$( '.codemirror' ).each(
				function() {
					var options = {
						mode: 'javascript',
						lineNumbers: true
					};
					var myCodeMirror = CodeMirror.fromTextArea( this, options );
				}
			);

			ccb.checkForSubscription();
			ccb.toggleAdvanced( $( '#clipchamp-show-advanced' ).is( ':checked' ) );
			ccb.registerEventHandlers();

			$( '.conditional-settings' ).hide();
			$( '#' + $( '.output-select' ).val() + '_settings' ).show();

			// TODO:Disable fields when API key is not set
			if ( $( '#ccb_field-apiKey' ).val() == "" ) {
				$( '#ccb_settings input' ).attr( 'disabled', true );
			}
		},

		/**
		 * Registers event handlers
		 */
		registerEventHandlers: function () {
			$( '#upload-button' ).click( ccb.initMediaUploader );
			$( '#ccb_field-output' ).change( ccb.changeOutput );
			$( '#ccb_field-fps' ).change( ccb.changeFramerate );
			window.addEventListener( 'message', ccb.afterLogin );
			$( '#update-plan' ).click( ccb.updateSubscription );
			$( '#configureAPI' ).click( ccb.afterAPISettings );
			$( '#configurePlugin' ).click( ccb.afterPluginSettings );
			$( '#clipchamp-show-advanced' ).change( ccb.updateAdvanced );
		},

		/**
		 * Initialize the media uploader
		 *
		 * @param object event
		 */
		initMediaUploader: function( event ) {
			event.preventDefault();
			if ( ccb.mediaUploader ) {
				ccb.mediaUploader.open();
				return;
			}
			ccb.mediaUploader = wp.media.frames.file_frame = wp.media(
				{
					title: 'Choose Logo',
					button: {
						text: 'Choose Logo'
					},
					multiple: false,
					library: {
						type: 'image'
					}
				}
			);

			ccb.mediaUploader.on(
				'select', function() {
					var attachment = ccb.mediaUploader.state().get( 'selection' ).first().toJSON();
					$( '.media-uploader' ).val( attachment.url );
				}
			);
			ccb.mediaUploader.open();
		},

		changeOutput: function( event ) {
			var output = $( event.target ).val();
			$( '.conditional-settings' ).hide();
			$( '#' + output + '_settings' ).show();
		},

		changeFramerate: function ( event ) {
			var fps = $( event.target ).val();
			if ( fps == 'custom' ) {
				$( '#ccb_field-fps-custom' ).show();
			} else {
				$( '#ccb_field-fps-custom' ).hide();
			}
		},

		checkForSubscription: function() {
			var subscription = subscription || window.subscription || clipchamp.subscription;
			if ( ! subscription ) {
				return;
			}
			ccb.linkAccount( subscription );
		},

		afterLogin: function( event ) {
			var origin = event.origin || event.originalEvent.origin;
			if ( origin !== 'https://login.clipchamp.com' ) {
				return;
			}
			var subscription = event.data.subscription;
			if ( ! subscription ) {
				return;
			}
			ccb.linkAccount( subscription );
		},

		linkAccount: function( subscription ) {
			if ( subscription.plan.family !== 'API' ) {
				$( '#wrongPlan' ).show();
				return;
			}

			if ( undefined === subscription.plan_id || undefined === subscription.api_key ) {
				return;
			}

			wp.ajax.send( 'ccb_link_account', {
				data: {
					subscription: subscription
				},
				success: function() {
					document.location.reload();
				}
			});
		},

		updateSubscription: function( event ) {
			event.preventDefault();
			$( '#update-plan' ).addClass( 'updating' );
			wp.ajax.send( 'ccb_update_plan', {
				complete: function() {
					document.location.reload();
				}
			});
		},

		afterAPISettings: function( event ) {
			$( '#step2' ).hide();
			$( '#step3' ).show();
			$( '.steps__item--active' ).toggleClass( 'steps__item--active steps__item--done' ).next().addClass( 'steps__item--active' );
		},

		afterPluginSettings: function( event ) {
			$( '#step3' ).hide();
			$( '#step4' ).show();
			$( '.steps__item--active' ).toggleClass( 'steps__item--active steps__item--done' );
			$( '.steps' ).addClass( 'steps--done' );
		},

		updateAdvanced: function( event ) {
			var checked = $( event.target ).is( ':checked' );
			ccb.toggleAdvanced( checked );
			ccb.saveAdvanced( checked );
		},

		toggleAdvanced: function( show ) {
			if ( show ) {
				$( '.advanced-only' ).show();
			} else {
				$( '.advanced-only' ).hide();
			}
		},

		saveAdvanced: function( checked ) {
			wp.ajax.send( 'save_advanced', {
				data: {
					clipchamp_show_advanced: checked
				}
			});
		}
	}; // end ccb

	$( document ).ready( ccb.init );

} // end ccbWrapper()

ccbWrapper( jQuery );
