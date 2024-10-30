<?php

if ( ! class_exists( 'CCB_Shortcode' ) ) {

	class CCB_Shortcode extends CCB_Module {

		protected static $default_label;
		protected static $default_size;
		protected static $default_color;

		const SHORTCODE_TAG        = 'clipchamp';
		const PLUGIN_SCRIPT_HANDLE = 'clipchamp-tinymce';

		/*
		 * General methods
		 */

		/**
		 * Constructor
		 *
		 * @mvc Controller
		 */
		protected function __construct() {
			$this->register_hook_callbacks();
		}

		/*
		 * Static methods
		 */

		/**
		 * Defines the shortcode
		 *
		 * @mvc Controller
		 *
		 * @param array $attributes
		 * @return string
		 */
		public static function render_shortcode( $attributes = array() ) {
			$attributes = apply_filters( 'ccb_shortcode-attributes', $attributes );
			return CCB_Uploader::render( $attributes );
		}

		/**
		 * Register a TinyMCE plugin
		 *
		 * @param array $plugins
		 * @return array $plugins
		 */
		public static function register_tinymce_plugin( $plugins ) {
			wp_localize_script(
				'common',
				'clipchamp',
				array(
					'title'        => __( 'Insert Clipchamp Video Uploader', 'clipchamp' ),
					'defaultLabel' => self::$default_label,
					'defaultSize'  => self::$default_size,
					'defaultColor' => self::$default_color,
				)
			);
			$plugins['clipchamp'] = plugins_url( 'javascript/tinymce.js', dirname( __FILE__ ) );

			return $plugins;
		}

		/**
		 * Register a TinyMCE toolbar button
		 *
		 * @param array $buttons
		 * @return array $buttons
		 */
		public static function register_tinymce_toolbar_buttons( $buttons ) {
			array_push( $buttons, '|', 'clipchamp' );
			return $buttons;
		}

		/**
		 * Render the button settings modal
		 */
		public static function render_button_settings() {
			?>
			<div id="clipchamp-button-settings" style="display:none;">
				<p class="clipchamp-button-label">
					<label for="clipchamp_button_label" style="display: block;"><?php esc_html_e( 'Label', 'clipchamp' ); ?></label>
					<input name="clipchamp_button_label" type="text" id="clipchamp_button_label" value="<?php echo esc_attr( self::$default_label ); ?>">
				</p>
				<p class="clipchamp-button-size">
					<label for="clipchamp_button_size" style="display: block;"><?php esc_html_e( 'Size', 'clipchamp' ); ?></label>
					<select name="clipchamp_button_size" id="clipchamp_button_size">
						<option value="tiny" <?php selected( 'tiny', self::$default_size ); ?>>
							<?php esc_html_e( 'Tiny', 'clipchamp' ); ?>
						</option>
						<option value="small" <?php selected( 'small', self::$default_size ); ?>>
							<?php esc_html_e( 'Small', 'clipchamp' ); ?>
						</option>
						<option value="medium" <?php selected( 'medium', self::$default_size ); ?>>
							<?php esc_html_e( 'Medium', 'clipchamp' ); ?>
						</option>
						<option value="large" <?php selected( 'large', self::$default_size ); ?>>
							<?php esc_html_e( 'Large', 'clipchamp' ); ?>
						</option>
					</select>
				</p>
				<p><input type="button" class="button" id="clipchamp-button-insert" value="Insert"></p>
			</div>
			<?php
		}

		/**
		 * Register editor stylesheet
		 *
		 * @param string $editor_css
		 * @return string
		 */
		public static function add_editor_styles( $editor_css ) {
			$editor_css .= ', ' . plugins_url( 'css/editor.css', dirname( __FILE__ ) );
			return $editor_css;
		}

		/*
		 * Instance methods
		 */

		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'init', array( $this, 'init' ) );
			add_shortcode( self::SHORTCODE_TAG, __CLASS__ . '::render_shortcode' );
			if ( 'true' === get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins', __CLASS__ . '::register_tinymce_plugin' );
				add_filter( 'mce_buttons', __CLASS__ . '::register_tinymce_toolbar_buttons' );
				add_action( 'edit_form_after_editor', __CLASS__ . '::render_button_settings' );
				add_filter( 'mce_css', __CLASS__ . '::add_editor_styles' );
			}
		}

		/**
		 * Prepares site to use the plugin during activation
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		public function deactivate() {
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {
			// TODO: Use CCB_Settings
			$settings            = get_option( 'ccb_settings' );
			self::$default_label = $settings['appearance']['field-label'];
			self::$default_size  = $settings['appearance']['field-size'];
			self::$default_color = $settings['appearance']['field-color'];
		}

		/**
		 * Executes the logic of upgrading from specific older versions of the plugin to the current version
		 *
		 * @mvc Model
		 *
		 * @param integer $db_version
		 */
		public function upgrade( $db_version = 0 ) {
		}

		/**
		 * Checks that the object is in a correct state
		 *
		 * @mvc Model
		 *
		 * @param string $property An individual property to check, or 'all' to check all of them
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			return true;
		}
	}

}
