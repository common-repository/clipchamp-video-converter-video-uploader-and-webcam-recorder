<?php
/*
* Localization Section
*/
?>

<?php if ( 'ccb_field-btn_cancel_upload' === $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( 'ccb_field-btn_cancel_upload' ); ?>" name="<?php echo esc_attr( 'ccb_settings[localization][field-btn_cancel_upload]' ); ?>" class="regular-text" value="<?php echo esc_attr( $settings['field-btn_cancel_upload'] ); ?>" />

<?php endif; ?>
