<?php

if ( ! class_exists( 'CCB_Uploader' ) ) {

	class CCB_Uploader extends CCB_Module {

		protected static $id                   = 1;
		protected static $ajax_init            = false;
		protected static $readable_properties  = array( 'id', 'settings' );
		protected static $writeable_properties = array( 'id' );
		protected static $settings;

		const SCRIPT_HANDLE         = 'clipchamp-button';
		const PLUGIN_SCRIPT_HANDLE  = 'clipchamp-plugin';
		const SCRIPT_BASE_URL       = 'https://api.clipchamp.com/';
		const SCRIPT_FILE_NAME      = 'button.js';
		const ON_METADATA_AVAILABLE = 'ccbMetadataAvailable';
		const ON_PREVIEW_AVAILABLE  = 'ccbPreviewAvailable';
		const ON_VIDEO_CREATED      = 'ccbUploadVideo';
		const ON_UPLOAD_COMPLETE    = 'ccbUploadComplete';

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
		protected static function parse_button_atts( $atts, $expected ) {
			$args = array();

			if ( is_array( $atts ) || is_object( $atts ) ) {
				foreach ( $atts as $index => $att ) {
					if ( preg_match( '#^([^\(\)]+)(\(.+\)+)=["\'](.+)["\']$#', $att, $match ) ) {
						// We have an arrray attribute where $att is something like: foo(1)(2)="bar"
						preg_match_all( '/\(([^\(\)]+)\)/', $match[2], $nested, PREG_SET_ORDER, 0 );
						if ( count( $nested ) > 1 ) {
							$arg = &$args[ $match[1] ];
							foreach ( $nested as $nested_arg ) {
								$arg = &$arg[ $nested_arg[1] ];
							}
							$arg = rtrim( rtrim( $match[3], '"' ), "'" );
						} else {
							$args[ $match[1] ][ $nested[0][1] ] = rtrim( rtrim( $match[3], '"' ), "'" );
						}
					} else {
						// We have a simple attribute where $att is something like: foo="bar"
						$args[ $index ] = $att;
					}
				}
			}

			return self::extend_atts( $args, self::parse_settings( $expected ) );
		}

		protected static function extend_atts( $args, $defaults ) {
			foreach ( $defaults as $key => $value ) {
				if ( is_array( $value ) && isset( $args[ $key ] ) ) {
					$defaults[ $key ] = self::extend_atts( $args[ $key ], $defaults[ $key ] );
				} else {
					if ( isset( $args[ $key ] ) ) {
						$defaults[ $key ] = $args[ $key ];
					}
				}
				unset( $args[ $key ] );
			}
			foreach ( $args as $key => $value ) {
				$defaults[ $key ] = $value;
			}
			return $defaults;
		}

		/**
		 * Creates options for the Clipchamp API
		 *
		 * @mvc Controller
		 *
		 * @param $config
		 *
		 * @return string
		 */
		protected static function create_button_options( $config ) {
			if ( 0 === strcmp( $config['output'], 'blob' ) ) {
				$config['onVideoCreated'] = self::ON_VIDEO_CREATED;
			}
			$config['onMetadataAvailable'] = self::ON_METADATA_AVAILABLE;
			$config['onPreviewAvailable']  = self::ON_PREVIEW_AVAILABLE;
			$config['onUploadComplete']    = self::ON_UPLOAD_COMPLETE;

			$options = 'var options' . self::$id . ' = ' . wp_json_encode( $config, JSON_NUMERIC_CHECK ) . ';';
			$options = str_replace( '"' . self::ON_VIDEO_CREATED . '"', self::ON_VIDEO_CREATED, $options );
			$options = str_replace( '"' . self::ON_METADATA_AVAILABLE . '"', self::ON_METADATA_AVAILABLE, $options );
			$options = str_replace( '"' . self::ON_PREVIEW_AVAILABLE . '"', self::ON_PREVIEW_AVAILABLE, $options );
			$options = str_replace( '"' . self::ON_UPLOAD_COMPLETE . '"', self::ON_UPLOAD_COMPLETE, $options );

			return $options;
		}

		/**
		 * Parse the settings
		 *
		 * @param array $settings
		 *
		 * @return array
		 */
		protected static function parse_settings( $settings ) {
			$parsed   = array();
			$not_show = array( 'connect', 'post' );
			foreach ( $settings as $s_key => $section ) {
				if ( ! is_array( $section ) || in_array( $s_key, $not_show, true ) ) {
					continue;
				}
				foreach ( $section as $key => $value ) {
					if ( empty( $value ) ) {
						continue;
					}
					$key_arr = explode( '-', $key );
					array_shift( $key_arr );
					if ( in_array( $key_arr[0], $not_show, true ) ) {
						continue;
					}
					if ( count( $key_arr ) > 1 ) {
						$nested = &$parsed[ $key_arr[0] ];
						array_shift( $key_arr );
						foreach ( $key_arr as $sub_key ) {
							$nested = &$nested[ $sub_key ];
						}
						$nested = $value;
					} else {
						$parsed[ $key_arr[0] ] = $value;
					}
				}
			}

			return $parsed;
		}

		/**
		 * Renders the uploader button
		 *
		 * @mvc Controller
		 *
		 * @param array $attributes
		 *
		 * @return string
		 */
		public static function render( $attributes = array() ) {
			if ( empty( self::$settings['connect']['field-apiKey'] ) ) {
				return 'You need to enter your API key to use Clipchamp';
			}

			wp_enqueue_script( self::PLUGIN_SCRIPT_HANDLE );
			wp_enqueue_script( self::SCRIPT_HANDLE );
			if ( ! self::$ajax_init ) {
				wp_localize_script(
					self::SCRIPT_HANDLE,
					'ccb_ajax',
					array(
						'ajax_url'              => admin_url( 'admin-ajax.php' ),
						'upload_complete_nonce' => wp_create_nonce( 'ccb_upload_complete_nonce' ),
						'upload_image_nonce'    => wp_create_nonce( 'ccb_upload_image_nonce' ),
					)
				);
				self::$ajax_init = true;
			}

			$attributes = self::parse_button_atts( $attributes, self::$settings );
			$attributes = apply_filters( 'ccb_button-atts', $attributes );

			$js_script  = self::create_button_options( $attributes );
			$js_script .= 'var element' . self::$id . ' = document.getElementById("clipchamp-button-' . self::$id . '");';
			$js_script .= 'clipchamp(element' . self::$id . ', options' . self::$id . ');';

			if ( self::$id === 1 ) {
				// before upload
				if ( isset( self::$settings['posts'], self::$settings['posts']['field-before-create-hook'] ) || has_filter( 'ccb_before-create-hook' ) ) {
					$js_script .= 'ccbBeforeCreateHook = function(data) { ';
					$js_script .= apply_filters( 'ccb_before-create-hook', self::$settings['posts']['field-before-create-hook'] );
					$js_script .= '};';
				}
				// after upload
				if ( isset( self::$settings['posts'], self::$settings['posts']['field-after-create-hook'] ) || has_filter( 'ccb_after-create-hook' ) ) {
					$js_script .= 'ccbAfterCreateHook = function(postId, videoData, image) {';
					$js_script .= apply_filters( 'ccb_after-create-hook', self::$settings['posts']['field-after-create-hook'] );
					$js_script .= '};';
				}
			}

			if ( function_exists( 'wp_add_inline_script' ) ) {
				wp_add_inline_script( self::SCRIPT_HANDLE, $js_script );
			} else {
				self::add_inline_script( self::SCRIPT_HANDLE, $js_script );
			}

			return '<div id="clipchamp-button-' . esc_attr( self::$id ++ ) . '" class="clipchamp-button"></div><p></p>';
		}

		/**
		 * Add inline script to initialize Clipchamp button.
		 * Method for WordPress version prior 4.5.0.
		 *
		 * @mvc Controller
		 *
		 * @param string $handle Script handle
		 * @param string $data Inline script to be added
		 */
		public static function add_inline_script( $handle, $data ) {
			$handle = $handle . '-inline';

			$cb = function () use ( $handle, $data ) {
				if ( wp_script_is( $handle, 'done' ) ) {
					return;
				}
				echo "<script type=\"text/javascript\" id=\"js-$handle\">\n$data\n</script>\n"; // XSS okay.
				global $wp_scripts;
				$wp_scripts->done[] = $handle;
			};

			add_action( 'wp_print_footer_scripts', $cb );
		}

		/**
		 * Registers script for the button.
		 *
		 * @mvc Controller
		 */
		public static function register_scripts() {
			$api_key = self::$settings['connect']['field-apiKey'];

			wp_register_script(
				self::SCRIPT_HANDLE,
				self::SCRIPT_BASE_URL . $api_key . '/' . self::SCRIPT_FILE_NAME,
				array(),
				Clipchamp::VERSION,
				true
			);

			wp_register_script(
				self::PLUGIN_SCRIPT_HANDLE,
				plugins_url( 'javascript/button.js', dirname( __FILE__ ) ),
				array( 'jquery' ),
				Clipchamp::VERSION,
				true
			);
		}

		/**
		 * Returns json object containing the localization strings.
		 * This request is cached.
		 *
		 * @param string $locale
		 *
		 * @return string
		 */
		public static function get_localization( $locale ) {
			$localization = wp_cache_get( 'ccb_localization_' . $locale );
			if ( false === $localization ) {
				$file_name    = $locale . '.json';
				$localization = file_get_contents( $file_name );
				if ( $localization ) {
					$localization = json_decode( $localization );
					wp_cache_set( 'ccb_localization_' . $locale, $localization );

					return $localization;
				}
			}

			return false;
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
			add_action( 'wp_enqueue_scripts', __CLASS__ . '::register_scripts' );
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {
			// TODO: Use CCB_Settings
			self::$settings = get_option( 'ccb_settings' );
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
		 * @param string $property
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			unset( $property );
			return true;
		}

	}

}

if ( ! function_exists( 'get_clipchamp_button' ) ) {
	function get_clipchamp_button( $attributes = array() ) {
		return CCB_Uploader::render( $attributes );
	}
}

if ( ! function_exists( 'the_clipchamp_button' ) ) {
	function the_clipchamp_button( $attributes = array() ) {
		echo get_clipchamp_button( $attributes ); // XSS okay.
	}
}
