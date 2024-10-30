<?php

if ( ! class_exists( 'CBB_Block' ) ) {

	class CBB_Block extends CCB_Module {

		protected static $settings;
		protected static $default_label;
		protected static $default_color;

		const PLUGIN_SCRIPT_HANDLE = 'clipchamp-block';

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

		/**
		 * Registers script for the block.
		 *
		 * @mvc Controller
		 */
		public static function register_scripts() {
			wp_register_script(
				self::PLUGIN_SCRIPT_HANDLE,
				plugins_url( 'javascript/block.js', dirname( __FILE__ ) ),
				array( 'wp-i18n', 'wp-blocks', 'wp-element' )
			);
			wp_localize_script(
				self::PLUGIN_SCRIPT_HANDLE,
				'clipchamp',
				array(
					'defaultLabel' => self::$default_label,
				)
			);
			wp_enqueue_script( self::PLUGIN_SCRIPT_HANDLE );

			wp_enqueue_style(
				self::PLUGIN_SCRIPT_HANDLE,
				plugins_url( 'css/editor.css', dirname( __FILE__ ) ),
				array( 'wp-edit-blocks' ),
				filemtime( plugin_dir_path( dirname( __FILE__ ) ) . 'css/editor.css' )
			);

			$custom_css = '
				.wp-block-clipchamp-video-uploader span {
					background: ' . self::$default_color . ';
				}
			';
			wp_add_inline_style( self::PLUGIN_SCRIPT_HANDLE, $custom_css );
		}

		/**
		 * Registers the block type.
		 *
		 * @mvc Controller
		 */
		public static function register_blocks() {
			register_block_type(
				'clipchamp/video-uploader', array(
					'render_callback' => __CLASS__ . '::render',
				)
			);
		}

		/**
		 * Renders the block.
		 * Done server side so that there can be a different output depending on whether it's the admin or frontend.
		 *
		 * @param array $attributes
		 * @return string
		 * @mvc Controller
		 */
		public static function render( $attributes ) {
			$attributes['label'] = self::sanitize_label( $attributes['label'] );
			return CCB_Uploader::render( $attributes );
		}

		/**
		 * Since the label is an Editable, sometimes it's sent as an array, and other times as a string
		 * This method makes sure it's a string every time, and also sets the default value if there is none.
		 *
		 * @param mixed $label
		 * @return string
		 * @mvc Controller
		 */
		public static function sanitize_label( $label ) {
			// Check if the value is black, or an empty array
			// Can't use empty() here, since it would match the string "0"
			if ( '' === $label || ( is_array( $label ) && empty( $label ) ) ) {
				$label = self::$default_label;
			}
			if ( is_array( $label ) ) {
				$label = $label[0];
			}

			return $label;
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
			add_action( 'init', __CLASS__ . '::register_blocks' );
			add_action( 'enqueue_block_editor_assets', __CLASS__ . '::register_scripts' );
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
			self::$settings      = get_option( 'ccb_settings' );
			self::$default_label = self::$settings['appearance']['field-label'];
			self::$default_color = self::$settings['appearance']['field-color'];
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
		 *
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			return true;
		}
	}
}
