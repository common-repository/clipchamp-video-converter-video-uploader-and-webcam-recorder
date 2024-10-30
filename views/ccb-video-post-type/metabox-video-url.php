<table id="ccb_video-url-box" class="form-table">
	<tbody>
	<tr>
		<th>
			<label for="ccb_video-url">Video URL:</label>
		</th>
		<td>
			<?php wp_nonce_field( 'ccb_video-url-nonce', '_ccb_video-url' ); ?>
			<input id="ccb_video-url" name="ccb_video-url" type="text" class="regular-text" value="<?php echo esc_attr( $video_url ); ?>" disabled />
		</td>
	</tr>
	</tbody>
</table>
