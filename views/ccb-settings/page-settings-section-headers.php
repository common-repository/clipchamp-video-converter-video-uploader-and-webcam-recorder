<?php if ( 'ccb_section-connect' === $section['id'] ) : ?>

	<?php
	$plan = get_option( 'ccb_plan' );
	if ( isset( $plan['name'] ) && isset( $plan['renewal_date'] ) ) {
		?>
		<div id="subscription-data">
			<p>
				<strong><?php esc_html_e( 'My Subscription', 'clipchamp' ); ?>: </strong>
				<?php esc_html_e( 'API', 'clipchamp' ); ?> <?php echo esc_html( $plan['name'] ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Renewal Date', 'clipchamp' ); ?>: </strong>
				<?php echo esc_html( date( 'F j, Y', strtotime( $plan['renewal_date'] ) ) ); ?>
			</p>
			<p><a href="#" id="update-plan"><span class="dashicons dashicons-update"></span> Refresh Subscription Data</a></p>
		</div>
		<?php
	}
	?>
	<p>
		Enter your API key here to activate the plugin. You can obtain it from your API account on clipchamp.com that
		you opened during the setup of the plugin.
	</p>

<?php elseif ( 'ccb_section-appearance' === $section['id'] ) : ?>

	<p>Adjust visual and textual elements of the video button and user interface.</p>

<?php elseif ( 'ccb_section-style' === $section['id'] ) : ?>

	<p class="advanced-only">
		UI themes are available in all plans. Custom CSS styling is available in the plugin’s Business and Enterprise
		plans.
	</p>
	<p class="advanced-only">
		It allows for the detailed customization of the plugin’s visual appearance using CSS. You can provide a custom
		CSS stylesheet as a URL (Stylesheet URL setting) or as a CSS declarations string (Stylesheet setting). In both
		cases, the custom CSS declarations augment but do not replace the default user interface styling, based on the
		Bootstrap 3 default styling. That is, the custom CSS styling is layered on top of the existing CSS styling,
		where existing CSS classes can be augmented, CSS properties be added or replaced.
	</p>
	<p>
		Note that the style does not apply to the embedded button.
	</p>

<?php elseif ( 'ccb_section-video' === $section['id'] ) : ?>

	<p>
		Adjust settings around input and output options, as well as the upload destination for videos your users submit.
	</p>

<?php elseif ( 'ccb_section-advanced' === $section['id'] ) : ?>

	<p>
		Enable expert settings of the video recorder and uploader that can be useful in specific scenarios you’d like to
		achieve. Note that some additional expert settings of the Clipchamp API require manual setup and coding outside
		of the plugin for WordPress. The <a href="https://clipchamp.com/en/api" target="_blank">Clipchamp API</a> is the
		product that our video recording plugin for WordPress is based on.
	</p>

<?php elseif ( 'ccb_section-posts' === $section['id'] ) : ?>

	<p>
		Each time a user uploads a video, a video post gets created - see “Clipchamp Videos” in the WP list of settings
		on the left. You can manage the default settings for these posts here.
	</p>

<?php elseif ( 'ccb_section-s3' === $section['id'] ) : ?>

	<p>Configuration elements when using Amazon S3 upload target. (<a
				href="https://clipchamp.com/blog/uploading-videos-from-clipchamp-button-to-aws-s3" target="_blank">configuration
			instructions for S3</a>)</p>

<?php elseif ( 'ccb_section-azure' === $section['id'] ) : ?>

	<p>Configuration elements when using Microsoft Azure blob storage upload target. (<a
				href="https://clipchamp.com/blog/uploading-videos-from-clipchamp-button-to-windows-azure"
				target="_blank">configuration instructions for Azure</a>)</p>

<?php elseif ( 'ccb_section-gdrive' === $section['id'] ) : ?>

	<p>Configuration elements when using Google Drive upload target.</p>

<?php elseif ( 'ccb_section-youtube' === $section['id'] ) : ?>

	<p>Configuration elements when using YouTube upload target.</p>

<?php elseif ( 'ccb_section-dropbox' === $section['id'] ) : ?>

	<p>Configuration elements when using Dropbox upload target.</p>

<?php endif; ?>
