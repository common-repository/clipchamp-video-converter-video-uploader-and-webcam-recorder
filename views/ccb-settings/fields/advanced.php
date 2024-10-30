<?php
/*
 * Video Section
 */
?>

<?php if ( 'ccb_field-enable' === $field['label_for'] ) : ?>

	<?php
	$advanced = array(
		'batch',
		'mobile-webcam-format-fallback',
		'no-error-bypass',
		'no-hidden-run',
		'no-popout',
		'no-probe-reject',
	);
	?>

	<?php foreach ( $default_sets['enable'] as $enable => $enable_label ) : ?>
		<p class="<?php echo in_array( $enable, $advanced, true ) ? 'advanced-only' : ''; ?>">
			<input id="<?php echo esc_attr( 'ccb_field-enable-' . $enable ); ?>" name="<?php echo esc_attr( 'ccb_settings[advanced][field-enable][]' ); ?>" type="checkbox" value="<?php echo esc_attr( $enable ); ?>" <?php checked( true, in_array( $enable, $settings['field-enable'], true ) ); ?> />
			<label for="<?php echo esc_attr( 'ccb_field-enable-' . $enable ); ?>"><?php echo esc_attr( $enable_label ); ?></label>
		</p>
		<p class="description <?php echo in_array( $enable, $advanced, true ) ? 'advanced-only' : ''; ?>">
			<?php
			switch ( $enable ) {
				case 'batch':
					esc_attr_e( 'Let users select multiple input videos at once to be uploaded to you in sequence without further user interaction.' );
					break;
				case 'mobile-webcam-format-fallback':
					esc_attr_e( 'Enable compression of webcam recordings on mobile devices even when the target format is not available (experimental). This feature is currently restricted to Chrome on Android where with this flag, the Clipchamp API produces WebM files using the VP8 or VP9 video codec.' );
					break;
				case 'no-branding':
					echo 'Remove Clipchamp branding from the user interface - available in our <a href="https://clipchamp.com/en/pricing/api-access" target="_blank">Business and Enterprise plans</a>. This setting does not apply to the embedded button. Use the Custom Button API option to style the embedded button - Business and Enterprise plans.';
					break;
				case 'no-error-bypass':
					esc_attr_e( 'If transcoding fails for whatever reason, normally Clipchamp would resort to simply uploading the input file as is. This flag suppresses that behavior. The most common cause for transcoding failures are unsupported input codecs.' );
					break;
				case 'no-hidden-run':
					esc_attr_e( 'Disable the option to continue processing and uploading in the background if the user closes the window after clicking submit.' );
					break;
				case 'no-popout':
					esc_attr_e( 'Some browsers block the use of certain features for third party code, when this is detected Clipchamp will open a new window in order to gain access to these features. Setting no-popout suppresses this behavior, and forces Clipchamp to try to make do with what\'s available. This might lead to increased memory requirements, among other things.' );
					break;
				case 'no-probe-reject':
					esc_attr_e( 'In case we are unable to analyse the input video file a user wants to upload, it would normally get rejected as it might be corrupted or contain an unknown codec. Enabling this option accepts all video files and skips straight to uploading them without client-side processing.' );
					break;
				case 'no-thank-you':
					esc_attr_e( 'Disable the thank you screen and close window immediately. If there were any errors encountered during the process the last screen will still be displayed.' );
					break;
			}
			?>
		</p>
	<?php endforeach; ?>
	<input name="<?php esc_attr_e( 'ccb_settings[advanced][field-enable][]' ); ?>" type="hidden" value=""  />

<?php endif; ?>

<?php if ( 'ccb_field-experimental' === $field['label_for'] ) : ?>

	<?php foreach ( $default_sets['experimental'] as $experimental => $experimental_label ) : ?>
		<p>
			<input id="<?php echo esc_attr( 'ccb_field-experimental-' . $experimental ); ?>" name="<?php echo esc_attr( 'ccb_settings[advanced][field-experimental][]' ); ?>" type="checkbox" value="<?php echo esc_attr( $experimental ); ?>" <?php checked( true, in_array( $experimental, $settings['field-experimental'], true ) ); ?> />
			<label for="<?php echo esc_attr( 'ccb_field-experimental-' . $experimental ); ?>"><?php echo esc_attr( $experimental_label ); ?></label>
		</p>
		<p class="description">
			<?php
			switch ( $experimental ) {
				case 'force-popout':
					esc_attr_e( 'Always launch the user interface of the Clipchamp API in a separate "popout" browser window, even if it could run inside an iframe inside the embedding website\'s DOM. Must not be used in conjunction with the no-popout flag (enable) parameter.' );
					break;
				case 'overlong-recording':
					esc_attr_e( 'Allow webcam/mobile camera recordings without any timely limitation of the recording duration (as otherwise enforced by the Clipchamp API). The recording duration can still be deliberately limited by setting a numeric value (number of seconds) in the camera.limit parameter. Clients need to make sure to only set the overlong-recording flag in supported browsers (currently: Chrome, Opera, and Firefox).' );
					break;
				case 'h264-hardware-acceleration':
					esc_attr_e( 'Enable hardware-accelerated H.264 video encoding on supported platforms (currently: x86-based ChromeOS/Chromebook devices). The flag only applies to the web (default) preset and the mp4 (default) format. Depending on the underlying hardware, a multiple times speedup can be attained when setting the h264-hardware-acceleration flag. Clients will experience different compression ratio for the same (subjective) perceived output quality and are thus encouraged to adjust the compression parameter to yield an acceptable quality/compression tradeoff.' );
					break;
			}
			?>
		</p>
	<?php endforeach; ?>
	<input name="<?php esc_attr_e( 'ccb_settings[advanced][field-experimental][]' ); ?>" type="hidden" value=""  />

<?php endif; ?>

<?php if ( 'ccb_field-camera-limit' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-camera-limit' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[advanced][field-camera-limit]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-camera-limit'] ); ?>"/>
	<p class="description">
		Set a maximum recording time per webcam recording in seconds. By default, the max. recording time is set to 300
		seconds (5 minutes). Setting a lower value is available in all plans, longer recordings than 5 minutes are
		available in the Business and Enterprise plans.
	</p>

<?php endif; ?>

<?php
/*
* Posts Section
*/
?>

<?php if ( 'ccb_field-show-with-posts' === $field['label_for'] ) : ?>

	<p>
		<input id="<?php esc_attr_e( 'ccb_field-show-with-posts' ); ?>" name="<?php esc_attr_e( 'ccb_settings[advanced][field-show-with-posts][]' ); ?>" type="checkbox" value="1" <?php checked( true, $settings['field-show-with-posts'] ); ?> />
	</p>
	<p class="description">
		Include video posts in blog roll, categories etc.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-post-status' === $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'ccb_field-post-status' ); ?>" name="<?php esc_attr_e( 'ccb_settings[advanced][field-post-status]' ); ?>" class="output-select">
		<?php foreach ( $default_sets['post_statuses'] as $status => $status_label ) : ?>
			<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $settings['field-post-status'], $status ); ?>><?php echo esc_attr( $status_label ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		The status of a Video Post after a video was uploaded by a user on your website and a Video Post was created. Warning - if you set this to “Publish”, every video users upload to you will automatically be publicly visible.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-post-category' === $field['label_for'] ) : ?>

	<?php
	$categories = get_categories(
		array(
			'hide_empty' => false,
		)
	);
	?>
	<select id="<?php esc_attr_e( 'ccb_field-post-category' ); ?>" name="<?php esc_attr_e( 'ccb_settings[advanced][field-post-category]' ); ?>" class="output-select">
		<?php foreach ( $categories as $category ) : ?>
			<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( $settings['field-post-category'], $category->term_id ); ?>><?php echo esc_attr( $category->name ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		The default category of a post created after a video was uploaded.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-before-create-hook' === $field['label_for'] ) : ?>

	<textarea id="<?php echo esc_attr( 'ccb_field-before-create-hook' ); ?>" name="<?php echo esc_attr( 'ccb_settings[advanced][field-before-create-hook]' ); ?>" rows="10" class="codemirror"><?php echo esc_attr( $settings['field-before-create-hook'] ) ?></textarea>
	<p class="description">
		The JavaScript code you specify here will be executed before a video post is created. You have access to the <code>data</code>
		variable, which includes information about the uploaded video. You can add WordPress post parameters to the data
		variable to store them with the video post (see
		<a href="https://developer.wordpress.org/reference/functions/wp_insert_post/" target="_blank">WordPress Code Reference</a>
		). Please always end your custom code with <code>return data;</code>.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-after-create-hook' === $field['label_for'] ) : ?>

	<textarea id="<?php echo esc_attr( 'ccb_field-after-create-hook' ); ?>" name="<?php echo esc_attr( 'ccb_settings[advanced][field-after-create-hook]' ); ?>" rows="10" class="codemirror"><?php echo esc_attr( $settings['field-after-create-hook'] ) ?></textarea>
	<p class="description">
		The JavaScript code you specify here will be executed after a video post is created. You have access to the <code>postId</code>,
		<code>videoData</code> and <code>image</code> variables.
	</p>

<?php endif; ?>
