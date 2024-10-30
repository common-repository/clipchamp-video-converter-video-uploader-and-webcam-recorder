<?php

if ( ! class_exists( 'CBB_Gravity_Forms_AddOn' ) && class_exists( 'GFAddOn' ) ) {

	class CBB_Gravity_Forms_AddOn extends GFAddOn {

		protected $_version                  = Clipchamp::VERSION;
		protected $_min_gravityforms_version = '1.9';
		protected $_slug                     = 'clipchamp';
		protected $_path                     = 'clipchamp/classes';
		protected $_full_path                = __FILE__;
		protected $_title                    = 'Gravity Forms Clipchamp Add-On';
		protected $_short_title              = 'Clipchamp Add-On';

		/**
		 * @var object $_instance If available, contains an instance of this class.
		 */
		private static $_instance = null;

		/**
		 * Returns an instance of this class, and stores it in the $_instance property.
		 *
		 * @return object $_instance An instance of this class.
		 */
		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Include the field early so it is available when entry exports are being performed.
		 */
		public function pre_init() {
			parent::pre_init();

			if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
				require_once 'ccb-gravity-forms-field.php';
				GF_Fields::register( new CBB_Gravity_Forms_Field() );
			}
		}

		public function init_admin() {
			parent::init_admin();
			add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
			add_action( 'gform_field_standard_settings', array( $this, 'field_standard_settings' ), 10, 2 );
			add_action( 'gform_field_appearance_settings', array( $this, 'field_appearance_settings' ), 10, 2 );
		}

		/**
		 * Include editor.css when the form contains a 'clipchamp' type field.
		 *
		 * @return array
		 */
		public function styles() {
			$styles = array(
				array(
					'handle'  => 'clipchamp-gravity-forms',
					'src'     => $this->get_base_url() . '/../css/editor.css',
					'version' => $this->_version,
					'enqueue' => array(
						array(
							'admin_page' => array( 'form_editor' ),
						),
					),
				),
			);
			return array_merge( parent::styles(), $styles );
		}

		/**
		 * Include gravityforms.js when the form contains a 'clipchamp' type field.
		 *
		 * @return array
		 */
		public function scripts() {
			$scripts = array(
				array(
					'handle'  => 'clipchamp_gravityforms',
					'src'     => $this->get_base_url() . '/../javascript/gravityforms.js',
					'version' => $this->_version,
					'strings' => array(
						'onlyOneField' => __( 'Only one Video Upload field can be added to the form', 'clipchamp' ),
					),
					'enqueue' => array(
						array(
							'admin_page' => array( 'form_editor' ),
						),
					),
				),
			);
			return array_merge( parent::scripts(), $scripts );
		}

		/**
		 * Add the tooltips for the field.
		 *
		 * @param array $tooltips An associative array of tooltips where the key is the tooltip name and the value is the tooltip.
		 *
		 * @return array
		 */
		public function tooltips( $tooltips ) {
			$clipchamp_tooltips = array(
				'button_size_setting' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Button Size', 'clipchamp' ), esc_html__( 'The size of the Clipchamp upload button.', 'clipchamp' ) ),
			);
			return array_merge( $tooltips, $clipchamp_tooltips );
		}

		/**
		 * Add the custom setting for the Clipchamp field to the General tab.
		 *
		 * @param int $position The position the settings should be located at.
		 * @param int $form_id The ID of the form currently being edited.
		 */
		public function field_standard_settings( $position, $form_id ) {
			// Add our Button Label setting just before the 'Field Label' setting.
			if ( 10 === $position ) {
				?>
				<li class="button_label_setting field_setting">
					<label for="button_label_setting" class="section_label">
						<?php esc_html_e( 'Button Label', 'clipchamp' ); ?>
						<?php gform_tooltip( 'button_label_setting' ); ?>
					</label>
					<input id="button_label_setting" type="text" value="" class="fieldwidth-3" onkeyup="SetButtonLabelSetting(this);" onchange="SetButtonLabelSetting(this);"/>
				</li>
				<?php
			}
		}

		/**
		 * Add the custom setting for the Clipchamp field to the Appearance tab.
		 *
		 * @param int $position The position the settings should be located at.
		 * @param int $form_id The ID of the form currently being edited.
		 */
		public function field_appearance_settings( $position, $form_id ) {
			// Add our Button Size setting just before the 'Field Size' setting.
			if ( 300 === $position ) {
				?>
				<li class="button_size_setting field_setting">
					<label for="button_size_setting" class="section_label">
						<?php esc_html_e( 'Button Size', 'clipchamp' ); ?>
						<?php gform_tooltip( 'button_size_setting' ); ?>
					</label>
					<select id="button_size_setting" onchange="SetButtonSizeSetting(this);">
						<option value="tiny">
							<?php esc_html_e( 'Tiny', 'clipchamp' ); ?>
						</option>
						<option value="small">
							<?php esc_html_e( 'Small', 'clipchamp' ); ?>
						</option>
						<option value="medium">
							<?php esc_html_e( 'Medium', 'clipchamp' ); ?>
						</option>
						<option value="large">
							<?php esc_html_e( 'Large', 'clipchamp' ); ?>
						</option>
					</select>
				</li>
				<?php
			}
		}
	}
}
