/* global clipchamp */
var clipchamp = clipchamp;
tinymce.PluginManager.add(
	'clipchamp', function( editor, url ) {

		var clipchampButtonInsert = document.getElementById( 'clipchamp-button-insert' );

		if ( null !== clipchampButtonInsert ) {
			document.getElementById( 'clipchamp-button-insert' ).addEventListener(
				'click', function() {
					// Allow word characters, numbers, whitespace, underscores
					var label = document.getElementById( 'clipchamp_button_label' ).value.replace( /[^\w\s]/gi, '' ),
						size  = document.getElementById( 'clipchamp_button_size' ).value.replace( /[^\w\s]/gi, '' );

					if ( '' === label ) {
						label = clipchamp.defaultLabel;
					}

					if ( '' === size ) {
						label = clipchamp.defaultSize;
					}

					tb_remove();
					insertShortcode( label, size );
				}
			);

			editor.addButton(
				'clipchamp', {
					title: 'Clipchamp Uploader',
					cmd: 'clipchamp',
					image: url + '/../images/icon.png'
				}
			);

			editor.addCommand(
				'clipchamp', function() {
					openModal();
				}
			);

			editor.on(
				'BeforeSetContent', function( event ) {
					event.content = replaceShortcodes( event.content )
				}
			);

			editor.on(
				'PostProcess', function( event ) {
					if ( event.get ) {
						event.content = restoreShortcodes( event.content );
					}
				}
			);
		}

		function openModal() {
			var thickbox_url = '#TB_inline?width=300&height=180&inlineId=clipchamp-button-settings';
			tb_show( clipchamp.title, thickbox_url );

			// @see https://core.trac.wordpress.org/ticket/17249
			// document.getElementById( 'TB_window' ).setAttribute( 'style', 'width: 300px; height: 180px; margin-left: -150px;' );
			document.getElementById( 'TB_window' ).style.width = '300px';
			document.getElementById( 'TB_window' ).style.height = '250px';
			document.getElementById( 'TB_window' ).style.marginLeft = '-175px';
			document.getElementById( 'TB_window' ).style.marginTop = ( ( window.innerHeight / 2 ) - 200 ) + 'px';

			document.getElementById( 'clipchamp_button_label' ).focus();
		}

		function insertShortcode( label, size ) {
			// Insert selected text back into editor, wrapping it in an anchor tag
			editor.execCommand( 'mceInsertContent', false, '[clipchamp label="' + label + '" size="' + size + '"]' );
		}

		function replaceShortcodes( content ) {
			return content.replace(
				/\[clipchamp([^\]]*)\]/g, function( match ) {
					var data       = window.encodeURIComponent( match ),
					labelMatch = match.match( /label="\b[^"]*"/g ),
					sizeMatch  = match.match( /size="\b[^"]*"/g ),
					label      = clipchamp.defaultLabel,
					size       = clipchamp.defaultSize,
					color      = clipchamp.defaultColor;

					if ( null !== labelMatch && labelMatch.length > 0 ) {
						label = labelMatch[0].substring( 7, labelMatch[0].length - 1 );
					}

					if ( null !== sizeMatch && sizeMatch.length > 0 ) {
						size = sizeMatch[0].substring( 6, sizeMatch[0].length - 1 );
					}

					return '<p><input class="clipchamp-button mceItem ' + size + '" style="background-color: ' + color + '" type="button" value="' + label + '" data-shortcode="' + data + '" data-mce-resize="false" data-mce-placeholder="1"></p>';
				}
			);
		}

		function restoreShortcodes( content ) {
			function getAttr( str, name ) {
				name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
				return name ? window.decodeURIComponent( name[1] ) : '';
			}

			return content.replace(
				/<input class="clipchamp-button(.+?)>/g, function( match ) {
					var data = getAttr( match, 'data-shortcode' );

					if ( data ) {
						return '<p>' + data + '</p>';
					}

					return match;
				}
			);
		}
	}
);
