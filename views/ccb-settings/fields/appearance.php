<?php
/*
* Appearance Section
*/
?>

<?php if ( 'ccb_field-label' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-label' ); ?>" name="<?php echo esc_attr( 'ccb_settings[appearance][field-label]' ); ?>" class="regular-text" value="<?php echo esc_attr( $settings['field-label'] ); ?>" />
	<p class="description">
		The text that appears on the video button you embed in posts and pages. E.g. “Record a video now”
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-size' === $field['label_for'] ) : ?>
	<select id="<?php esc_attr_e( 'ccb_field-size' ); ?>" name="<?php esc_attr_e( 'ccb_settings[appearance][field-size]' ); ?>">
		<?php foreach ( $default_sets['sizes'] as $size ) : ?>
			<option value="<?php echo esc_attr( $size ); ?>" <?php selected( $settings['field-size'], $size ); ?>><?php echo esc_html( ucfirst( $size ) ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		Select 1 of 4 available sizes for the embedded video button.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-title' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-title' ); ?>" name="<?php echo esc_attr( 'ccb_settings[appearance][field-title]' ); ?>" class="regular-text" value="<?php echo esc_attr( $settings['field-title'] ); ?>" />
	<p class="description">This title appears at the top of the recording and uploading interface your users see after they click an embedded video button.</p>

<?php endif; ?>

<?php if ( 'ccb_field-logo' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-logo' ); ?>" name="<?php echo esc_attr( 'ccb_settings[appearance][field-logo]' ); ?>" class="regular-text media-uploader" value="<?php echo esc_attr( $settings['field-logo'] ); ?>" />
	<input id="upload-button" type="button" class="button" value="Choose Logo" />
	<p class="description">
		This image is shown in the top left corner of the recording and uploading interface. Select the URL of a logo or image to use. Max. dimensions: 150x30
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-color' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-color' ); ?>" name="<?php echo esc_attr( 'ccb_settings[appearance][field-color]' ); ?>" class="color-field" value="<?php echo esc_attr( $settings['field-color'] ); ?>" />
	<p class="description">
		Determines the color for the embedded button, the recording interface’s title bar and other graphical elements. Can be a color name (e.g. “blue”, “red”), a hex-encoded color code (e.g. <code>#3300cc</code>), or an RGB-encoded color (e.g. <code>rgba(78,24,212,0.5)</code>).
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-style-url' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-style-url' ); ?>" name="<?php echo esc_attr( 'ccb_settings[appearance][field-style-url]' ); ?>" class="regular-text" value="<?php echo esc_attr( $settings['field-style-url'] ); ?>" />
	<p class="description">
		The URL of a custom CSS stylesheet that is loaded after the Clipchamp plugin’s default CSS styling. Must not be used in combination with the Stylesheet setting. Gets overridden if Theme is set above.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-style-text' === $field['label_for'] ) : ?>

	<textarea id="<?php echo esc_attr( 'ccb_field-style-text' ); ?>" name="<?php echo esc_attr( 'ccb_settings[appearance][field-style-text]' ); ?>" rows="10" class="codemirror"><?php echo esc_attr( $settings['field-style-text'] ); ?></textarea>
	<p class="description">
		A custom CSS stylesheet, given as a string. The custom CSS stylesheet is instantiated after the Clipchamp plugin’s default styling. Must not be used in combination with Stylesheet URL. Gets overridden if Theme is set above.
	</p>

<?php endif; ?>

<?php if ( 'ccb_field-theme' === $field['label_for'] ) : ?>

	<select id="<?php esc_attr_e( 'ccb_field-theme' ); ?>" name="<?php echo esc_attr( 'ccb_settings[appearance][field-theme]' ); ?>">
		<?php foreach ( $default_sets['themes'] as $theme => $theme_label ) : ?>
			<option value="<?php echo esc_attr( $theme ); ?>" <?php selected( $settings['field-style-url'], $theme ); ?>><?php echo esc_attr( $theme_label ); ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description">Select 1 of 4 available UI themes to change the style of the recording & uploading interface - <a href="https://util.clipchamp.com/en/api-setup/install" target="_blank">see here for previews</a>. Some themes might not work with the primary color you selected. WARNING: Overrides Stylesheet URL and Stylesheet Text if some are set below.</p>

<?php endif; ?>
