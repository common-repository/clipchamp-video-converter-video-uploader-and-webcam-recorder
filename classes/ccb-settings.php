<?php

if ( ! class_exists( 'CCB_Settings' ) ) {

	/**
	 * Handles plugin settings and user profile meta fields
	 */
	class CCB_Settings extends CCB_Module {
		protected $settings;
		protected static $settings_page;
		protected static $readable_properties  = array( 'settings' );
		protected static $writeable_properties = array( 'settings' );
		protected static $default_settings;
		// TODO: Populate this from apiUtils.js
		protected static $default_sets = array(
			'sizes'         => array( 'tiny', 'small', 'medium', 'large' ),
			'presets'       => array( 'web', 'mobile', 'windows', 'animation' ),
			'formats'       => array( 'webm', 'mp4', 'flv', 'asf', 'gif' ),
			'resolutions'   => array( 'keep', '240p', '360p', '480p', '720p', '1080p', '320w', '640w' ),
			'themes'        => array(
				'' => 'None',
				'https://api.clipchamp.com/static/button/themes/modern-light.css' => 'Modern Light',
				'https://api.clipchamp.com/static/button/themes/modern-dark.css' => 'Modern Dark',
				'https://api.clipchamp.com/static/button/themes/modern-camera.css' => 'Camera',
				'https://api.clipchamp.com/static/button/themes/modern-education.css' => 'Education',
			),
			'compressions'  => array( 'min', 'low', 'medium', 'high', 'max' ),
			'framerates'    => array(
				'keep'   => 'keep',
				'custom' => 'custom',
			),
			'inputs'        => array(
				'file'   => 'Upload File',
				'camera' => 'Record Camera',
			),
			'outputs'       => array(
				'blob'    => 'WordPress Media Library',
				'azure'   => 'Microsoft Azure',
				's3'      => 'Amazon S3',
				'youtube' => 'Youtube',
				'gdrive'  => 'Google Drive',
				'dropbox' => 'Dropbox',
			),
			'enable'        => array(
				'batch'                         => 'Allow batch upload',
				'mobile-webcam-format-fallback' => 'Mobile webcam format fallback',
				'no-branding'                   => 'No branding',
				'no-error-bypass'               => 'No error bypass',
				'no-hidden-run'                 => 'Disable background upload',
				'no-popout'                     => 'Disable popout fallback',
				'no-probe-reject'               => 'Accept all video files',
				'no-thank-you'                  => 'Disable thank you screen',
			),
			'experimental'  => array(
				'force-popout'               => 'Always launch UI in separate popout window',
				'h264-hardware-acceleration' => 'Enable hardware-accelerated H.264 encoding',
			),
			'post_statuses' => array(
				'publish' => 'Publish',
				'draft'   => 'Draft',
				'pending' => 'Pending',
				'private' => 'Private',
			),
			's3_regions'    => array(
				'us-east-1'      => 'US East (N. Virginia)',
				'us-east-2'      => 'US East (Ohio)',
				'us-west-1'      => 'US West (N. California)',
				'us-west-2'      => 'US West (Oregon)',
				'ca-central-1'   => 'Canada (Central)',
				'ap-south-1'     => 'Asia Pacific (Mumbai)',
				'ap-northeast-2' => 'Asia Pacific (Seoul)',
				'ap-southeast-1' => 'Asia Pacific (Singapore)',
				'ap-southeast-2' => 'Asia Pacific (Sydney)',
				'ap-northeast-1' => 'Asia Pacific (Tokyo)',
				'eu-central-1'   => 'EU (Frankfurt)',
				'eu-west-1'      => 'EU (Ireland)',
				'eu-west-2'      => 'EU (London)',
				'sa-east-1'      => 'South America (São Paulo)',
			),
		);

		const REQUIRED_CAPABILITY = 'administrator';
		const TRANSIENT           = 'ccb_activation_redirect';


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
		 * Public setter for protected variables
		 *
		 * Updates settings outside of the Settings API or other subsystems
		 *
		 * @mvc Controller
		 *
		 * @param string $variable
		 * @param array  $value This will be merged with WPPS_Settings->settings, so it should mimic the structure of the WPPS_Settings::$default_settings. It only needs the contain the values that will change, though. See WordPress_Plugin_Skeleton->upgrade() for an example.
		 */
		public function __set( $variable, $value ) {
			// Note: WPPS_Module::__set() is automatically called before this
			if ( 'settings' !== $variable ) {
				return;
			}

			$this->settings = self::validate_settings( $value );
			update_option( 'ccb_settings', $this->settings );
		}

		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'admin_menu', __CLASS__ . '::register_settings_pages' );

			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			if ( get_transient( self::TRANSIENT ) ) {
				add_action( 'admin_init', __CLASS__ . '::activation_redirect' );
			}

			add_filter(
				'plugin_action_links_' . plugin_basename( dirname( __DIR__ ) ) . '/bootstrap.php',
				__CLASS__ . '::add_plugin_action_links'
			);

			add_filter( 'screen_options_show_screen', __CLASS__ . '::show_screen_options_tab' );
			add_filter( 'screen_settings', __CLASS__ . '::register_screen_options' );
			add_action( 'wp_ajax_save_advanced', __CLASS__ . '::save_screen_options' );
		}

		/**
		 * Prepares site to use the plugin during activation
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {
			set_transient( self::TRANSIENT, true, 30 );
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		public function deactivate() {
			// Remove settings
			delete_option( 'ccb_settings' );
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {
			self::$default_settings = self::get_default_settings();
			$this->settings         = self::get_settings();
		}

		/**
		 * Executes the logic of upgrading from specific older versions of the plugin to the current version
		 *
		 * @mvc Model
		 *
		 * @param int|string $db_version
		 */
		public function upgrade( $db_version = 0 ) {
			/*
			if( version_compare( $db_version, 'x.y.z', '<' ) )
			{
				// Do stuff
			}
			*/
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
			// Note: __set() calls validate_settings(), so settings are never invalid
			return true;
		}


		/*
		 * Static methods
		 */

		/**
		 * Redirect to the Clipchamp Settings page on activation
		 */
		public static function activation_redirect() {
			if ( ! get_transient( self::TRANSIENT ) ) {
				return;
			}

			delete_transient( self::TRANSIENT );

			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) { // CSRF okay.
				return;
			}

			wp_safe_redirect(
				add_query_arg(
					array(
						'page' => 'ccb_settings',
						'tab'  => 'connect_settings',
					), admin_url( 'options-general.php' )
				)
			);
		}

		/*
		 * Plugin Settings
		 */

		/**
		 * Establishes initial values for all settings
		 *
		 * @mvc Model
		 *
		 * @return array
		 */
		protected static function get_default_settings() {
			// Connect,  Appearance,  Input & Output, Upload Destination, Advanced
			// $connect, $appearance, $video,         $destination,       $advanced

			$connect = array(
				'field-apiKey'    => null,
				'field-apiSecret' => null,
			);

			$appearance = array(
				'field-label'      => 'Upload with Clipchamp!',
				'field-size'       => self::$default_sets['sizes'][1],
				'field-title'      => 'Ye\' olde video-upload shoppe',
				'field-logo'       => 'https://api.clipchamp.com/static/button/images/logo.svg',
				'field-color'      => '#303030',
				'field-style-url'  => '',
				'field-style-text' => '',
			);

			$inputs     = array_keys( self::$default_sets['inputs'] );
			$framerates = array_keys( self::$default_sets['framerates'] );

			$video = array(
				'field-preset'      => self::$default_sets['presets'][0],
				'field-format'      => self::$default_sets['formats'][0],
				'field-resolution'  => self::$default_sets['resolutions'][0],
				'field-compression' => self::$default_sets['compressions'][2],
				'field-fps'         => $framerates[0],
				'field-inputs'      => array( $inputs[0], $inputs[1] ),
			);

			$outputs = array_keys( self::$default_sets['outputs'] );

			$destination = array(
				'field-output'              => $outputs[3],
				'field-s3-region'           => '',
				'field-s3-bucket'           => '',
				'field-s3-folder'           => '',
				'field-azure-container'     => '',
				'field-azure-folder'        => '',
				'field-gdrive-folder'       => '',
				'field-youtube-title'       => '',
				'field-youtube-description' => '',
				'field-dropbox-folder'      => '',
			);

			$advanced = array(
				'field-enable'             => array(),
				'field-experimental'       => array(),
				'field-camera-limit'       => '',
				'field-show-with-posts'    => false,
				'field-post-status'        => 'pending',
				'field-post-category'      => 1,
				'field-before-create-hook' => '',
				'field-after-create-hook'  => '',
			);

			return array(
				'connect'     => $connect,
				'appearance'  => $appearance,
				'video'       => $video,
				'destination' => $destination,
				'advanced'    => $advanced,
			);
		}

		/**
		 * Retrieves all of the settings from the database
		 *
		 * @mvc Model
		 *
		 * @return array
		 */
		public static function get_settings() {
			$settings = shortcode_atts(
				self::$default_settings,
				get_option( 'ccb_settings', array() )
			);

			return $settings;
		}

		/**
		 * Adds links to the plugin's action link section on the Plugins page
		 *
		 * @mvc Model
		 *
		 * @param array $links The links currently mapped to the plugin
		 * @return array
		 */
		public static function add_plugin_action_links( $links ) {
			array_unshift( $links, '<a href="https://util.clipchamp.com/developers" target="_blank">Help</a>' );
			array_unshift( $links, '<a href="options-general.php?page=ccb_settings">Settings</a>' );

			return $links;
		}

		/**
		 * Adds pages to the Admin Panel menu
		 *
		 * @mvc Controller
		 */
		public static function register_settings_pages() {
			self::$settings_page = add_submenu_page(
				'options-general.php',
				CCB_NAME . ' Settings',
				CCB_NAME,
				self::REQUIRED_CAPABILITY,
				'ccb_settings',
				__CLASS__ . '::markup_settings_page'
			);
		}

		/**
		 * Show the Screen Options tab at the top of the screen
		 */
		public static function show_screen_options_tab( $show_screen ) {
			$screen = get_current_screen();

			if ( ! is_object( $screen ) || self::$settings_page !== $screen->id ) {
				return $show_screen;
			}

			return true;
		}

		/**
		 * Adds screen options to the admin
		 */
		public static function register_screen_options() {
			$screen = get_current_screen();

			if ( ! is_object( $screen ) || self::$settings_page !== $screen->id ) {
				return;
			}

			$show_advanced = get_option( 'clipchamp_show_advanced' );

			return '
				<fieldset>
					<legend>' . __( 'Clipchamp', 'clipchamp' ) . '</legend>
					<label>
						<input name="clipchamp-show-advanced" type="checkbox" id="clipchamp-show-advanced"
							   value="1" ' . checked( $show_advanced, '1', false ) . '>' .
							__( 'Show Advanced Settings', 'clipchamp' ) . '
					</label>
				</fieldset>
				';
		}

		/**
		 * Save screen options
		 */
		public static function save_screen_options() {
			$checked = sanitize_key( $_POST['clipchamp_show_advanced'] );

			$show_advanced = '0';
			if ( 'true' === $checked ) {
				$show_advanced = 1;
			}

			update_option( 'clipchamp_show_advanced', $show_advanced );

			wp_send_json_success();
		}

		/**
		 * Creates the markup for the Settings page
		 *
		 * @mvc Controller
		 */
		public static function markup_settings_page() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				echo self::render_template( 'ccb-settings/page-settings.php' ); // XSS okay.
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Registers settings sections, fields and settings
		 *
		 * @mvc Controller
		 */
		public function register_settings() {
			/*
			 * General Section
			 */
			add_settings_section(
				'ccb_section-connect',
				'Connect',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_connect'
			);

			add_settings_field(
				'ccb_field-apiKey',
				'API Key*',
				array( $this, 'markup_fields' ),
				'ccb_settings_connect',
				'ccb_section-connect',
				array(
					'label_for' => 'ccb_field-apiKey',
				)
			);

			/*
			 * Appearance Section
			 */
			add_settings_section(
				'ccb_section-appearance',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_appearance'
			);

			add_settings_field(
				'ccb_field-label',
				'Button Label*',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_appearance',
				'ccb_section-appearance',
				array(
					'label_for' => 'ccb_field-label',
				)
			);

			add_settings_field(
				'ccb_field-size',
				'Button Size*',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_appearance',
				'ccb_section-appearance',
				array(
					'label_for' => 'ccb_field-size',
				)
			);

			add_settings_field(
				'ccb_field-title',
				'Popup Title*',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_appearance',
				'ccb_section-appearance',
				array(
					'label_for' => 'ccb_field-title',
				)
			);

			add_settings_field(
				'ccb_field-logo',
				'Popup Logo*',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_appearance',
				'ccb_section-appearance',
				array(
					'label_for' => 'ccb_field-logo',
				)
			);

			add_settings_field(
				'ccb_field-color',
				'Primary Color*',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_appearance',
				'ccb_section-appearance',
				array(
					'label_for' => 'ccb_field-color',
				)
			);

			add_settings_section(
				'ccb_section-style',
				'Style',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_style'
			);

			add_settings_field(
				'ccb_field-theme',
				'UI Theme',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_style',
				'ccb_section-style',
				array(
					'label_for' => 'ccb_field-theme',
				)
			);

			add_settings_section(
				'ccb_section-stylesheet',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_stylesheet'
			);

			add_settings_field(
				'ccb_field-style-url',
				'Stylesheet URL',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_stylesheet',
				'ccb_section-stylesheet',
				array(
					'label_for' => 'ccb_field-style-url',
				)
			);

			add_settings_field(
				'ccb_field-style-text',
				'Stylesheet',
				array( $this, 'markup_appearance_fields' ),
				'ccb_settings_stylesheet',
				'ccb_section-stylesheet',
				array(
					'label_for' => 'ccb_field-style-text',
				)
			);

			/*
			 * Video Section
			 */
			add_settings_section(
				'ccb_section-format',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_format'
			);

			add_settings_field(
				'ccb_field-preset',
				'Video Encoding Preset*',
				array( $this, 'markup_video_fields' ),
				'ccb_settings_format',
				'ccb_section-format',
				array(
					'label_for' => 'ccb_field-preset',
				)
			);

			add_settings_field(
				'ccb_field-format',
				'Output Video Format*',
				array( $this, 'markup_video_fields' ),
				'ccb_settings_format',
				'ccb_section-format',
				array(
					'label_for' => 'ccb_field-format',
				)
			);

			add_settings_section(
				'ccb_section-video',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_video'
			);

			add_settings_field(
				'ccb_field-resolution',
				'Output Video Resolution*',
				array( $this, 'markup_video_fields' ),
				'ccb_settings_video',
				'ccb_section-video',
				array(
					'label_for' => 'ccb_field-resolution',
				)
			);

			add_settings_field(
				'ccb_field-compression',
				'Output Video Compression*',
				array( $this, 'markup_video_fields' ),
				'ccb_settings_video',
				'ccb_section-video',
				array(
					'label_for' => 'ccb_field-compression',
				)
			);

			add_settings_field(
				'ccb_field-fps',
				'Output Video Frame Rate*',
				array( $this, 'markup_video_fields' ),
				'ccb_settings_video',
				'ccb_section-video',
				array(
					'label_for' => 'ccb_field-fps',
				)
			);

			add_settings_section(
				'ccb_section-input',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_input'
			);

			add_settings_field(
				'ccb_field-inputs',
				'Input Options*',
				array( $this, 'markup_video_fields' ),
				'ccb_settings_input',
				'ccb_section-input',
				array(
					'label_for' => 'ccb_field-inputs',
				)
			);

			/*
			 * Destination Section
			 */
			add_settings_section(
				'ccb_section-destination',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_destination'
			);

			add_settings_field(
				'ccb_field-output',
				'Upload Destination*',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_destination',
				'ccb_section-destination',
				array(
					'label_for' => 'ccb_field-output',
				)
			);

			/*
			 * S3 Section
			 */
			add_settings_section(
				'ccb_section-s3',
				'S3 Settings',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_s3'
			);

			add_settings_field(
				'ccb_field-s3-region',
				'S3 Region*',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_s3',
				'ccb_section-s3',
				array(
					'label_for' => 'ccb_field-s3-region',
				)
			);

			add_settings_field(
				'ccb_field-s3-bucket',
				'S3 Bucket*',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_s3',
				'ccb_section-s3',
				array(
					'label_for' => 'ccb_field-s3-bucket',
				)
			);

			add_settings_field(
				'ccb_field-s3-folder',
				'S3 Folder',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_s3',
				'ccb_section-s3',
				array(
					'label_for' => 'ccb_field-s3-folder',
				)
			);

			/*
			 * Azure Section
			 */
			add_settings_section(
				'ccb_section-azure',
				'Azure Settings',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_azure'
			);

			add_settings_field(
				'ccb_field-azure-container',
				'Azure Container*',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_azure',
				'ccb_section-azure',
				array(
					'label_for' => 'ccb_field-azure-container',
				)
			);

			add_settings_field(
				'ccb_field-azure-folder',
				'Azure Folder',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_azure',
				'ccb_section-azure',
				array(
					'label_for' => 'ccb_field-azure-folder',
				)
			);

			/*
			 * Google Drive Section
			 */
			add_settings_section(
				'ccb_section-gdrive',
				'Google Drive Settings',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_gdrive'
			);

			add_settings_field(
				'ccb_field-gdrive-folder',
				'Google Drive Folder',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_gdrive',
				'ccb_section-gdrive',
				array(
					'label_for' => 'ccb_field-gdrive-folder',
				)
			);

			/*
			 * Youtube Section
			 */
			add_settings_section(
				'ccb_section-youtube',
				'Youtube Settings',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_youtube'
			);

			add_settings_field(
				'ccb_field-youtube-title',
				'Youtube Title',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_youtube',
				'ccb_section-youtube',
				array(
					'label_for' => 'ccb_field-youtube-title',
				)
			);

			add_settings_field(
				'ccb_field-youtube-description',
				'Youtube Description',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_youtube',
				'ccb_section-youtube',
				array(
					'label_for' => 'ccb_field-youtube-description',
				)
			);

			/*
			 * Dropbox Section
			 */
			add_settings_section(
				'ccb_section-dropbox',
				'Dropbox Settings',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_dropbox'
			);

			add_settings_field(
				'ccb_field-dropbox-folder',
				'Dropbox Folder',
				array( $this, 'markup_destination_fields' ),
				'ccb_settings_dropbox',
				'ccb_section-dropbox',
				array(
					'label_for' => 'ccb_field-dropbox-folder',
				)
			);

			/*
			 * Advanced Section
			 */
			add_settings_section(
				'ccb_section-enable',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_enable'
			);

			add_settings_field(
				'ccb_field-enable',
				'Behaviour',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_enable',
				'ccb_section-enable',
				array(
					'label_for' => 'ccb_field-enable',
				)
			);

			add_settings_section(
				'ccb_section-advanced',
				'',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_advanced'
			);

			add_settings_field(
				'ccb_field-before-create-hook',
				'Before Create Hook',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_advanced',
				'ccb_section-advanced',
				array(
					'label_for' => 'ccb_field-before-create-hook',
				)
			);

			add_settings_field(
				'ccb_field-after-create-hook',
				'After Create Hook',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_advanced',
				'ccb_section-advanced',
				array(
					'label_for' => 'ccb_field-after-create-hook',
				)
			);

			add_settings_field(
				'ccb_field-experimental',
				'Experimental Features',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_advanced',
				'ccb_section-advanced',
				array(
					'label_for' => 'ccb_field-experimental',
				)
			);

			add_settings_field(
				'ccb_field-camera-limit',
				'Recording Limit',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_advanced',
				'ccb_section-advanced',
				array(
					'label_for' => 'ccb_field-camera-limit',
				)
			);

			/*
			 * Posts Section
			 */
			add_settings_section(
				'ccb_section-posts',
				'Video Posts',
				__CLASS__ . '::markup_section_headers',
				'ccb_settings_posts'
			);

			add_settings_field(
				'ccb_field-show-with-posts',
				'Show Videos with Posts',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_posts',
				'ccb_section-posts',
				array(
					'label_for' => 'ccb_field-show-with-posts',
				)
			);

			add_settings_field(
				'ccb_field-post-status',
				'Status',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_posts',
				'ccb_section-posts',
				array(
					'label_for' => 'ccb_field-post-status',
				)
			);

			add_settings_field(
				'ccb_field-post-category',
				'Category',
				array( $this, 'markup_advanced_fields' ),
				'ccb_settings_posts',
				'ccb_section-posts',
				array(
					'label_for' => 'ccb_field-post-category',
				)
			);

			register_setting(
				'ccb_settings_appearance',
				'ccb_settings_appearance',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_video',
				'ccb_settings_video',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_output',
				'ccb_settings_output',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_s3',
				'ccb_settings_s3',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_azure',
				'ccb_settings_azure',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_youtube',
				'ccb_settings_youtube',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_gdrive',
				'ccb_settings_gdrive',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_dropbox',
				'ccb_settings_dropbox',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_advanced',
				'ccb_settings_advanced',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_posts',
				'ccb_settings_posts',
				array( $this, 'validate_settings' )
			);

			register_setting(
				'ccb_settings_js_hooks',
				'ccb_settings_js_hooks',
				array( $this, 'validate_settings' )
			);

			// The settings container
			register_setting(
				'ccb_settings',
				'ccb_settings',
				array( $this, 'validate_settings' )
			);
		}

		/**
		 * Adds the section introduction text to the Settings page
		 *
		 * @mvc Controller
		 *
		 * @param array $section
		 */
		public static function markup_section_headers( $section ) {
			echo self::render_template(
				'ccb-settings/page-settings-section-headers.php', array(
					'section' => $section,
				), 'always'
			); // XSS okay.
		}

		/**
		 * Delivers the markup for settings fields
		 *
		 * @mvc Controller
		 *
		 * @param array $field
		 */
		public function markup_fields( $field ) {
			switch ( $field['label_for'] ) {
				case 'ccb_field-apiKey':
					// Do any extra processing here
					break;
			}

			echo self::render_template(
				'ccb-settings/page-settings-fields.php',
				array(
					'settings'     => $this->settings,
					'field'        => $field,
					'default_sets' => self::$default_sets,
				),
				'always'
			); // XSS okay.
		}

		/**
		 * Delivers the markup for appearance settings fields
		 *
		 * @mvc Controller
		 *
		 * @param array $field
		 */
		public function markup_appearance_fields( $field ) {
			echo self::render_template(
				'ccb-settings/fields/appearance.php',
				array(
					'settings'     => $this->settings['appearance'],
					'field'        => $field,
					'default_sets' => self::$default_sets,
				),
				'always'
			); // XSS okay.
		}

		/**
		 * Delivers the markup for video settings fields
		 *
		 * @mvc Controller
		 *
		 * @param array $field
		 */
		public function markup_video_fields( $field ) {
			echo self::render_template(
				'ccb-settings/fields/video.php',
				array(
					'settings'     => $this->settings['video'],
					'field'        => $field,
					'default_sets' => self::$default_sets,
					'api_key'      => $this->settings['connect']['field-apiKey'],
				),
				'always'
			); // XSS okay.
		}

		/**
		 * Delivers the markup for video settings fields
		 *
		 * @mvc Controller
		 *
		 * @param array $field
		 */
		public function markup_destination_fields( $field ) {
			echo self::render_template(
				'ccb-settings/fields/destination.php',
				array(
					'settings'     => $this->settings['destination'],
					'field'        => $field,
					'default_sets' => self::$default_sets,
					'api_key'      => $this->settings['connect']['field-apiKey'],
				),
				'always'
			); // XSS okay.
		}

		/**
		 * Delivers the markup for advanced settings fields
		 *
		 * @mvc Controller
		 *
		 * @param array $field
		 */
		public function markup_advanced_fields( $field ) {
			echo self::render_template(
				'ccb-settings/fields/advanced.php',
				array(
					'settings'     => $this->settings['advanced'],
					'field'        => $field,
					'default_sets' => self::$default_sets,
				),
				'always'
			); // XSS okay.
		}

		/**
		 * Validates submitted setting values before they get saved to the database. Invalid data will be overwritten with defaults.
		 *
		 * @mvc Model
		 *
		 * @param array $new_settings
		 * @return array
		 */
		public function validate_settings( $new_settings ) {
			$new_settings = shortcode_atts( $this->settings, $new_settings );

			if ( ! is_string( $new_settings['db-version'] ) ) {
				$new_settings['db-version'] = Clipchamp::VERSION;
			}

			/*
			 * Connect Settings
			 */
			if ( empty( $new_settings['connect']['field-apiKey'] ) ) {
				add_notice( 'API key cannot be empty', 'error' );
				$new_settings['connect']['field-apiKey'] = empty( $this->settings['connect']['field-apiKey'] ) ? self::$default_settings['connect']['field-apiKey'] : $this->settings['connect']['field-apiKey'];
			}
			if ( empty( $new_settings['connect']['field-apiSecret'] ) ) {
				add_notice( 'Error storing API details', 'error' );
				$new_settings['connect']['field-apiSecret'] = empty( $this->settings['connect']['field-apiSecret'] ) ? self::$default_settings['connect']['field-apiSecret'] : $this->settings['connect']['field-apiSecret'];
			}

			/*
			 * Appearance Settings
			 */
			if ( empty( $new_settings['appearance']['field-label'] ) ) {
				add_notice( 'Label cannot be empty', 'error' );
				$new_settings['appearance']['field-label'] = empty( $this->settings['appearance']['field-label'] ) ? self::$default_settings['connect']['field-label'] : $this->settings['appearance']['field-label'];
			}

			if ( ! in_array( $new_settings['appearance']['field-size'], self::$default_sets['sizes'], true ) ) {
				add_notice( 'Invalid value for size', 'error' );
				$new_settings['appearance']['field-size'] = empty( $this->settings['appearance']['field-size'] ) ? self::$default_settings['appearance']['field-size'] : $this->settings['appearance']['field-size'];
			}

			if ( empty( $new_settings['appearance']['field-title'] ) ) {
				add_notice( 'Title cannot be empty', 'error' );
				$new_settings['appearance']['field-title'] = empty( $this->settings['appearance']['field-title'] ) ? self::$default_settings['connect']['field-title'] : $this->settings['appearance']['field-title'];
			}

			// TODO: Check for URL
			if ( empty( $new_settings['appearance']['field-logo'] ) ) {
				add_notice( 'Logo cannot be empty', 'error' );
				$new_settings['appearance']['field-logo'] = empty( $this->settings['appearance']['field-logo'] ) ? self::$default_settings['connect']['field-logo'] : $this->settings['appearance']['field-logo'];
			}

			// TODO: Check for color
			if ( empty( $new_settings['appearance']['field-color'] ) ) {
				add_notice( 'Color cannot be empty', 'error' );
				$new_settings['appearance']['field-color'] = empty( $this->settings['appearance']['field-color'] ) ? self::$default_settings['connect']['field-color'] : $this->settings['appearance']['field-color'];
			}

			if ( ! empty( $new_settings['appearance']['field-theme'] ) ) {
				$new_settings['appearance']['field-style-url'] = $new_settings['appearance']['field-theme'];
				unset( $new_settings['appearance']['field-theme'] );
			}

			/*
			 * Video Settings
			 */
			if ( ! in_array( $new_settings['video']['field-preset'], self::$default_sets['presets'], true ) ) {
				add_notice( 'Invalid value for preset', 'error' );
				$new_settings['video']['field-preset'] = empty( $this->settings['video']['field-preset'] ) ? self::$default_settings['video']['field-preset'] : $this->settings['video']['field-preset'];
			}

			if ( ! in_array( $new_settings['video']['field-format'], self::$default_sets['formats'], true ) ) {
				add_notice( 'Invalid value for format', 'error' );
				$new_settings['video']['field-format'] = empty( $this->settings['video']['field-format'] ) ? self::$default_settings['video']['field-format'] : $this->settings['video']['field-format'];
			}

			if ( ! in_array( $new_settings['video']['field-resolution'], self::$default_sets['resolutions'], true ) ) {
				add_notice( 'Invalid value for resolution', 'error' );
				$new_settings['video']['field-resolution'] = empty( $this->settings['video']['field-resolution'] ) ? self::$default_settings['video']['field-resolution'] : $this->settings['video']['field-resolution'];
			}

			if ( ! in_array( $new_settings['video']['field-compression'], self::$default_sets['compressions'], true ) ) {
				add_notice( 'Invalid value for compression', 'error' );
				$new_settings['video']['field-compression'] = empty( $this->settings['video']['field-compression'] ) ? self::$default_settings['video']['field-compression'] : $this->settings['video']['field-compression'];
			}

			if ( in_array( $new_settings['video']['field-fps'], array_keys( self::$default_sets['framerates'] ), true ) ) {
				if ( 'custom' === $new_settings['video']['field-fps'] && ! empty( $new_settings['video']['field-fps-custom'] ) ) {
					$new_settings['video']['field-fps'] = floatval( $new_settings['video']['field-fps-custom'] );
					if ( 0 === intval( $new_settings['video']['field-fps'] ) ) {
						add_notice( 'Invalid value for framerate', 'error' );
						$new_settings['video']['field-fps'] = empty( $this->settings['video']['field-fps'] ) ? self::$default_settings['video']['field-fps'] : $this->settings['video']['field-fps'];
					}
				} else {
					if ( strcmp( $new_settings['video']['field-fps'], 'keep' ) !== 0 ) {
						add_notice( 'Invalid value for framerate', 'error' );
						$new_settings['video']['field-fps'] = empty( $this->settings['video']['field-fps'] ) ? self::$default_settings['video']['field-fps'] : $this->settings['video']['field-fps'];
					}
				}
				unset( $new_settings['video']['field-fps-custom'] );
			} else {
				add_notice( 'Invalid value for framerate', 'error' );
				$new_settings['video']['field-fps'] = empty( $this->settings['video']['field-fps'] ) ? self::$default_settings['video']['field-fps'] : $this->settings['video']['field-fps'];
			}

			if ( empty( $new_settings['video']['field-inputs'] ) ) {
				add_notice( 'Invalid value for inputs', 'error' );
				$new_settings['video']['field-inputs'] = empty( $this->settings['video']['field-inputs'] ) ? self::$default_settings['video']['field-inputs'] : $this->settings['video']['field-inputs'];
			}

			if ( ! in_array( $new_settings['destination']['field-output'], array_keys( self::$default_sets['outputs'] ), true ) ) {
				add_notice( 'Invalid value for destination', 'error' );
				$new_settings['destination']['field-output'] = empty( $this->settings['destination']['field-output'] ) ? self::$default_settings['destination']['field-output'] : $this->settings['destination']['field-output'];
			}

			if ( 0 === strcmp( $new_settings['destination']['field-output'], 's3' ) && empty( $new_settings['destination']['field-s3-bucket'] ) && empty( $new_settings['destination']['field-s3-region'] ) ) {
				add_notice( 'S3 region and bucket cannot be empty', 'error' );
				$new_settings['destination']['field-s3-region'] = empty( $this->settings['destination']['field-s3-region'] ) ? self::$default_settings['destination']['field-s3-region'] : $this->settings['destination']['field-s3-region'];
				$new_settings['destination']['field-s3-bucket'] = empty( $this->settings['destination']['field-s3-bucket'] ) ? self::$default_settings['destination']['field-s3-bucket'] : $this->settings['destination']['field-s3-bucket'];
				$new_settings['destination']['field-output']    = empty( $this->settings['destination']['field-output'] ) ? self::$default_settings['destination']['field-output'] : $this->settings['destination']['field-output'];
			}

			if ( 0 === strcmp( $new_settings['destination']['field-output'], 'azure' ) && empty( $new_settings['destination']['field-azure-container'] ) ) {
				add_notice( 'Azure container cannot be empty', 'error' );
				$new_settings['destination']['field-azure-container'] = empty( $this->settings['destination']['field-azure-container'] ) ? self::$default_settings['destination']['field-azure-container'] : $this->settings['destination']['field-azure-container'];
				$new_settings['destination']['field-output']          = empty( $this->settings['destination']['field-output'] ) ? self::$default_settings['destination']['field-output'] : $this->settings['destination']['field-output'];
			}

			/*
			 * Advanced Settings
			 */
			if ( ! empty( $new_settings['advanced']['field-enable'][0] ) ) {
				array_pop( $new_settings['advanced']['field-enable'] );
			}
			if ( ! empty( $new_settings['advanced']['field-experimental'][0] ) ) {
				array_pop( $new_settings['advanced']['field-experimental'] );
			}
			if ( empty( $new_settings['advanced']['field-show-with-posts'] ) || ! $new_settings['advanced']['field-show-with-posts'] ) {
				$new_settings['advanced']['field-show-with-posts'] = false;
			} else {
				$new_settings['advanced']['field-show-with-posts'] = true;
			}

			return $new_settings;
		}

	} // end CCB_Settings
}
