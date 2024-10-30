<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<div id="branding">
		<a href="https://clipchamp.com" target="_blank" class="logo"><img src="<?php echo esc_url( plugins_url( 'images/logo.svg', dirname( dirname( __FILE__ ) ) ) ); ?>" alt="Clipchamp" /></a>
	</div>
	<h1>Video Recorder and Uploader Settings</h1>

	<?php
	$settings = get_option( 'ccb_settings' );
	if ( empty( $settings['connect']['field-apiKey'] ) ) {
		$actual_link = add_query_arg(
			array(
				'page' => 'ccb_settings',
				'tab'  => 'connect_settings',
			), admin_url( 'options-general.php' )
		);

		printf(
			'<div class="updated notice">
				<h3><strong>%s</strong></h3>
				<p>%s</p>
				<p>
					<a href="#" onclick="window.open(\'%s\')" class="button button-primary">%s</a>
				</p>
			</div>',
			esc_html__( 'Log in to your Clipchamp account.', 'clipchamp' ),
			esc_html__( 'Don\'t have an account yet? Sign up for a free 14 day trial by clicking the button below. No credit card required.', 'clipchamp' ),
			esc_url( 'https://login.clipchamp.com?title=Login / Sign Up&plan=enterprise&redirect=' . rawurlencode( $actual_link ) ),
			esc_html__( 'Connect your Clipchamp account' )
		);

		printf(
			'<div class="notice error hidden" id="wrongPlan">
				<p class="alert__message">%s</p>
			</div>',
			sprintf(
				esc_html__( 'You already have a non-API account at clipchamp.com. Please %1$scontact us%2$s if you\'d like to trial the API & WordPress plugin.', 'clipchamp' ),
				'<a href="https://help.clipchamp.com/about-products-subscriptions/about-clipchamp/how-can-i-contact-you/">',
				'</a>'
			)
		);

	} else {
		$description = __( 'Embed a video button into posts or pages with the Clipchamp Uploader block.', 'clipchamp' );
		if ( ! function_exists( 'the_gutenberg_project' ) ) {
			$description = sprintf(
				// translators: placeholders are for code tags
				__( 'Embed a video button into posts or pages with the Clipchamp toolbar button, or with the %1$s[clipchamp]%2$s shortcode.', 'clipchamp' ),
				'<code>',
				'</code>'
			);
		}
		echo wp_kses_post(
			sprintf(
				'<p>%s</p>
			<p>%s</p>',
				$description,
				sprintf(
					// translators: placeholders are for anchor links
					 __( 'Click Screen Options in the top right to show all advanced settings. Please see the %1$sdocumentation%2$s for help</a>.', 'clipchamp' ),
					'<a href="https://help.clipchamp.com/api-and-wordpress-plugin#wordpress-plugin" target="_blank">',
					'</a>'
				)
			)
		);
	}
	?>

	<?php
	$plan = get_option( 'ccb_plan' );
	if ( isset( $plan['signup_status'] ) && isset( $plan['renewal_date'] ) && 'trialing' === $plan['signup_status'] ) {
		$now     = new DateTime();
		$renewal = new DateTime( $plan['renewal_date'] );

		if ( $now < $renewal ) {
			// Days Remaining

			$now->setTime( 0, 0 );
			$interval = $renewal->diff( $now );

			printf(
				'<div class="updated notice">
					<h3><strong>%s</strong></h3>
					<p>%s</p>
				</div>',
				sprintf(
					// translators: placeholder indicates a number of days
					esc_html__( '%s days remaining on your trial.', 'clipchamp' ),
					esc_html( $interval->format( '%a' ) )
				),
				sprintf(
					// translators: placeholders are for anchor links
					esc_html__( '%1$sComplete your signup%2$s for a smooth transition at the end of the trial period.', 'clipchamp' ),
					'<a href="https://util.clipchamp.com/en/profile" target="_blank">',
					'</a>'
				)
			);
		} else {
			// Trial Expired
			printf(
				'<div class="error notice">
					<h3><strong>%s</strong></h3>
					<p>%s</p>
				</div>',
				esc_html__( 'Your trial has expired.', 'clipchamp' ),
				sprintf(
					// translators: placeholders are for anchor links
					esc_html__( 'In order to keep using Clipchamp, please %1$sadd your payment details%2$s.', 'clipchamp' ),
					'<a href="https://util.clipchamp.com/en/profile" target="_blank">',
					'</a>'
				)
			);
		}
	}
	?>

	<?php $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'connect_settings'; // CSRF okay. ?>

	<form id="ccb_settings" method="post" action="options.php">
		<?php settings_fields( 'ccb_settings' ); ?>

		<h2 class="nav-tab-wrapper">
			<a href="?page=ccb_settings&tab=connect_settings" class="nav-tab <?php echo 'connect_settings' === $active_tab ? 'nav-tab-active' : ''; ?>">Connect</a>
			<a href="?page=ccb_settings&tab=appearance_settings" class="nav-tab <?php echo 'appearance_settings' === $active_tab ? 'nav-tab-active' : ''; ?>">Appearance</a>
			<a href="?page=ccb_settings&tab=video_settings" class="nav-tab <?php echo 'video_settings' === $active_tab ? 'nav-tab-active' : ''; ?>">Input & Output</a>
			<a href="?page=ccb_settings&tab=destination_settings" class="nav-tab <?php echo 'destination_settings' === $active_tab ? 'nav-tab-active' : ''; ?>">Upload Destination</a>
			<a href="?page=ccb_settings&tab=advanced_settings" class="nav-tab <?php echo 'advanced_settings' === $active_tab ? 'nav-tab-active' : ''; ?>">Advanced</a>
		</h2>

		<?php if ( 0 === strcmp( $active_tab, 'connect_settings' ) ) : ?>
			<?php do_settings_sections( 'ccb_settings_connect' ); ?>
		<?php endif; ?>

		<?php if ( 0 === strcmp( $active_tab, 'appearance_settings' ) ) : ?>
			<?php do_settings_sections( 'ccb_settings_appearance' ); ?>
			<?php do_settings_sections( 'ccb_settings_style' ); ?>
			<div class="advanced-only">
				<?php do_settings_sections( 'ccb_settings_stylesheet' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( 0 === strcmp( $active_tab, 'video_settings' ) ) : ?>
			<?php do_settings_sections( 'ccb_settings_format' ); ?>
			<div class="advanced-only">
				<?php do_settings_sections( 'ccb_settings_video' ); ?>
			</div>
			<?php do_settings_sections( 'ccb_settings_input' ); ?>
		<?php endif; ?>

		<?php if ( 0 === strcmp( $active_tab, 'destination_settings' ) ) : ?>
			<?php do_settings_sections( 'ccb_settings_destination' ); ?>
			<div id="s3_settings" class="conditional-settings">
				<?php do_settings_sections( 'ccb_settings_s3' ); ?>
			</div>
			<div id="azure_settings" class="conditional-settings">
				<?php do_settings_sections( 'ccb_settings_azure' ); ?>
			</div>
			<div id="youtube_settings" class="conditional-settings">
				<?php do_settings_sections( 'ccb_settings_youtube' ); ?>
			</div>
			<div id="gdrive_settings" class="conditional-settings">
				<?php do_settings_sections( 'ccb_settings_gdrive' ); ?>
			</div>
			<div id="dropbox_settings" class="conditional-settings">
				<?php do_settings_sections( 'ccb_settings_dropbox' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( 0 === strcmp( $active_tab, 'advanced_settings' ) ) : ?>
			<?php do_settings_sections( 'ccb_settings_enable' ); ?>
			<div class="advanced-only">
				<?php do_settings_sections( 'ccb_settings_advanced' ); ?>
			</div>
			<?php do_settings_sections( 'ccb_settings_posts' ); ?>
		<?php endif; ?>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
		</p>
	</form>
</div> <!-- .wrap -->
