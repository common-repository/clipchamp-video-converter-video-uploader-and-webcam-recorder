<?php
/*
 * General Section
 */
?>

<?php if ( 'ccb_field-apiKey' === $field['label_for'] ) : ?>

	<?php if ( empty( $settings['connect']['field-apiKey'] ) ) : ?>
		<?php
		$actual_link = add_query_arg(
			array(
				'page'   => 'ccb_settings',
				'tab'    => 'connect_settings',
				'action' => 'load_api_key',
			), admin_url( 'options-general.php' )
		);
		?>
		<p>
			<a href="#" onclick="window.open('https://login.clipchamp.com?title=Login / Sign Up&plan=enterprise&redirect=<?php echo rawurlencode( $actual_link ); ?>')" class="button">
				<?php esc_html_e( 'Connect your Clipchamp account', 'clipchamp' ); ?>
			</a>
		</p>
	<?php else : ?>
		<input id="<?php echo esc_attr( 'ccb_settings[connect][field-apiKey]' ); ?>" name="<?php echo esc_attr( 'ccb_settings[connect][field-apiKey]' ); ?>" class="regular-text" value="<?php echo esc_attr( $settings['connect']['field-apiKey'] ); ?>" />
		<p>
			<a href="https://util.clipchamp.com/en/profile" target="_blank" class="button">
				<?php esc_html_e( 'Manage subscription', 'clipchamp' ); ?>
			</a>
		</p>
		<input type="hidden" name="<?php echo esc_attr( 'ccb_settings[connect][field-apiSecret]' ); ?>" value="<?php echo esc_attr( $settings['connect']['field-apiSecret'] ); ?>" />
	<?php endif; ?>

<?php endif; ?>
