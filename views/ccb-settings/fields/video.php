<?php
/*
 * Video Section
 */
?>

<?php if ( 'ccb_field-preset' === $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'ccb_field-preset' ); ?>"
			name="<?php esc_attr_e( 'ccb_settings[video][field-preset]' ); ?>">
		<?php foreach ( $default_sets['presets'] as $preset ) : ?>
			<option value="<?php echo esc_attr( $preset ); ?>" <?php selected( $settings['field-preset'], $preset ); ?>><?php echo esc_html( $preset ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		The default and recommended preset to use is “web”. Encodes your users’ uploaded videos to be optimized for
		specific scenarios such as use on websites and video sharing sites such as YouTube.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-format' === $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'ccb_field-format' ); ?>"
			name="<?php esc_attr_e( 'ccb_settings[video][field-format]' ); ?>">
		<?php foreach ( $default_sets['formats'] as $format ) : ?>
			<option value="<?php echo esc_attr( $format ); ?>" <?php selected( $settings['field-format'], $format ); ?>><?php echo esc_attr( $format ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		You’ll receive user videos in this format. The recommended options are WebM or MP4 together with the “web” video encoding
		preset above. Note that FLV and ASF formats are not available in the plugin’s <a
				href="https://clipchamp.com/en/pricing/api-access" target="_blank">Entry plan</a>.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-resolution' === $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'ccb_field-resolution' ); ?>"
			name="<?php esc_attr_e( 'ccb_settings[video][field-resolution]' ); ?>">
		<?php foreach ( $default_sets['resolutions'] as $resolution ) : ?>
			<option value="<?php echo esc_attr( $resolution ); ?>" <?php selected( $settings['field-resolution'], $resolution ); ?>><?php echo esc_attr( $resolution ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		Videos your users upload are submitted at this resolution. All selections are downscale only, i.e. if a user
		submits a lower resolution video than your selected output resolution, the video will not be enlarged but
		submitted as is. Users can select videos at any input resolution (4k, 1080p, 720p,...) but the available max.
		output resolution depends on the Clipchamp plugin plan you subscribed to. E.g. if your plan includes 720p
		output, all webcam recordings and other videos users upload will get submitted to you at 720p. If a resolution
		that is not covered by your plan is selected, we will use the highest setting available in your plan.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-compression' === $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'ccb_field-compression' ); ?>"
			name="<?php esc_attr_e( 'ccb_settings[video][field-compression]' ); ?>">
		<?php foreach ( $default_sets['compressions'] as $compression ) : ?>
			<option value="<?php echo esc_attr( $compression ); ?>" <?php selected( $settings['field-compression'], $compression ); ?>><?php echo esc_attr( ucfirst( $compression ) ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		5 compression levels where “max” generally compresses videos the most and processes them the fastest at the
		expense of reducing visual quality. In turn, “min” compresses the least, takes longer to process but achieves
		the highest visual quality.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-fps' === $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'ccb_field-fps' ); ?>" name="<?php esc_attr_e( 'ccb_settings[video][field-fps]' ); ?>">
		<?php foreach ( $default_sets['framerates'] as $fps => $fps_label ) : ?>
			<option value="<?php echo esc_attr( $fps ); ?>" <?php echo ( $fps === $settings['field-fps'] || ( 'keep' !== $fps && 'keep' !== $settings['field-fps'] ) ) ? 'selected' : ''; ?>><?php echo esc_attr( $fps_label ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="text" id="<?php esc_attr_e( 'ccb_field-fps-custom' ); ?>"
		   name="<?php esc_attr_e( 'ccb_settings[video][field-fps-custom]' ); ?>"
		   style="<?php echo esc_attr( 'keep' === $settings['field-fps'] ? 'display: none' : '' ); ?>"
		   value="
			<?php
			if ( 'keep' !== $settings['field-fps'] ) {
				echo esc_attr( $settings['field-fps'] );}
?>
"/>
	<p class="description">
		If the frame rate of the input video exceeds this number it will be downsampled to match it. Videos that are
		already at a lower frame rate, or those where the input frame rate cannot be determined will be unaffected.
		Expert setting, recommended option is “keep”.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-inputs' === $field['label_for'] ) : ?>

	<?php foreach ( $default_sets['inputs'] as $input => $input_label ) : ?>
		<p>
			<input id="<?php echo esc_attr( 'ccb_field-inputs-' . $input ); ?>"
				   name="<?php echo esc_attr( 'ccb_settings[video][field-inputs][]' ); ?>" type="checkbox"
				   value="<?php echo esc_attr( $input ); ?>" <?php checked( true, in_array( $input, $settings['field-inputs'], true ) ); ?> />
			<label for="<?php echo esc_attr( 'ccb_field-inputs-' . $input ); ?>"><?php echo esc_attr( $input_label ); ?></label>
		</p>
	<?php endforeach; ?>
	<p class="description">
		Sources of videos that your users can select to submit videos to you. “Upload File” allows them to select
		existing video files from their device, “Record Camera” shows a webcam recorder as part of the Clipchamp plugin
		user interface so that users can record videos directly on your website.
	</p>

<?php endif; ?>
