<?php
/*
 * Destination Section
 */
?>

<?php if ( 'ccb_field-output' === $field['label_for'] ) : ?>

	<?php // TODO:Notify user of special conditions ?>
	<select id="<?php echo esc_attr( 'ccb_field-output' ); ?>"
			name="<?php echo esc_attr( 'ccb_settings[destination][field-output]' ); ?>" class="output-select">
		<?php foreach ( $default_sets['outputs'] as $output => $output_label ) : ?>
			<option value="<?php echo esc_attr( $output ); ?>" <?php selected( $settings['field-output'], $output ); ?>>
				<?php echo esc_attr( $output_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		The destination that user videos get submitted to. Availability can depend on the Clipchamp API plan you
		subscribed to. In addition to setting an upload destination here, make sure to complete the upload target setup
		in <a href="https://util.clipchamp.com/en/api-settings/integrations" target="_blank">your clipchamp.com account</a>
		as well. Otherwise the plugin will not be able to upload user videos to your account at the respective upload
		destination.
	</p>
	<?php if ( 0 === strcmp( $settings['field-output'], 'blob' ) ) : ?>
		<p>Upload Max Filesize: <code><?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?></code></p>
		<p>Post Max Size: <code><?php echo esc_html( ini_get( 'post_max_size' ) ); ?></code></p>
	<?php endif; ?>

<?php endif; ?>

<?php
/*
 * S3 Section
 */
?>

<?php if ( 'ccb_field-s3-region' === $field['label_for'] ) : ?>

	<select id="<?php echo esc_attr( 'ccb_field-s3-region' ); ?>"
			name="<?php echo esc_attr( 'ccb_settings[destination][field-s3-region]' ); ?>" class="output-select">
		<?php foreach ( $default_sets['s3_regions'] as $region => $region_label ) : ?>
			<option value="<?php echo esc_attr( $region ); ?>" <?php selected( $settings['field-s3-region'], $region ); ?>><?php echo esc_attr( $region_label ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		Please select the region of your bucket
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-s3-bucket' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-s3-bucket' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-s3-bucket]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-s3-bucket'] ); ?>"/>
	<p class="description">Target bucket for S3 upload. Value is required if uploading to S3.</p>

<?php endif; ?>

<?php if ( 'ccb_field-s3-folder' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-s3-folder' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-s3-folder]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-s3-folder'] ); ?>"/>
	<p class="description">(Optional) Target folder for S3 upload. If specified, uploaded files will be placed in this
		folder, i.e. the S3 key will have this as a prefix.</p>

<?php endif; ?>

<?php
/*
 * Azure Section
 */
?>

<?php if ( 'ccb_field-azure-container' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-azure-container' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-azure-container]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-azure-container'] ); ?>"/>
	<p class="description">Target container for blob storage upload. Value is required if uploading to Azure.</p>

<?php endif; ?>

<?php if ( 'ccb_field-azure-folder' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-azure-folder' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-azure-folder]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-azure-folder'] ); ?>"/>
	<p class="description">(Optional) Target folder for azure upload. If specified, uploaded files will be placed in
		this folder, i.e. the blob name will have this as a prefix.</p>

<?php endif; ?>

<?php
/*
 * Google Drive Section
 */
?>

<?php if ( 'ccb_field-gdrive-folder' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-gdrive-folder' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-gdrive-folder]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-gdrive-folder'] ); ?>"/>
	<p class="description">(Optional) Target folder for Google Drive upload. If specified, uploaded files will be placed
		in this folder. Relative to the globally configured root folder. If the folder doesn't exist, it will be
		created. Can be specified as a '/'-delimited path, or (e.g. if you have '/' in a folder name) using an array of
		strings. <strong>NOTE</strong>: We do cache the Google-assigned ID of the folder, which means if the folder is
		moved (or removed) we will continue uploading to it in its new location for some time.</p>

<?php endif; ?>

<?php
/*
 * Youtube Section
 */
?>

<?php if ( 'ccb_field-youtube-title' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-youtube-title' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-youtube-title]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-youtube-title'] ); ?>"/>
	<p class="description">(Optional) Assign this title to the video when it is uploaded.</p>

<?php endif; ?>

<?php if ( 'ccb_field-youtube-description' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-youtube-description' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-youtube-description]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-youtube-description'] ); ?>"/>
	<p class="description">(Optional) Assign this description to the video when it is uploaded.</p>

<?php endif; ?>

<?php
/*
 * Dropbox Section
 */
?>

<?php if ( 'ccb_field-dropbox-folder' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-dropbox-folder' ); ?>"
		   name="<?php echo esc_attr( 'ccb_settings[destination][field-dropbox-folder]' ); ?>" class="regular-text"
		   value="<?php echo esc_attr( $settings['field-dropbox-folder'] ); ?>"/>
	<p class="description">(Optional) If specified, files will be placed in this folder. If you already entered a folder
		in the <a href="https://util.clipchamp.com/en/api-settings/integrations" target="_blank">API settings</a>, there is
		no need to add the same folder name in here. If you add a folder name in here, it will be created as a subfolder
		to the one you specified in the <a href="https://util.clipchamp.com/en/api-settings/integrations" target="_blank">API
			settings</a>.
	</p>

<?php endif; ?>
