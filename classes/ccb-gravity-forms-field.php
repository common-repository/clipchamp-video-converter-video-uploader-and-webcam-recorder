<?php

if ( ! class_exists( 'CBB_Gravity_Forms_Field' ) && class_exists( 'GF_Field' ) ) {

	class CBB_Gravity_Forms_Field extends GF_Field {
		/**
		 * @var string $type The field type.
		 */
		public $type = 'clipchamp';

		protected static $default_label;
		protected static $default_size;
		protected static $default_color;

		public function __construct( $data = array() ) {
			parent::__construct( $data );

			// TODO: Use CCB_Settings
			$settings            = get_option( 'ccb_settings' );
			self::$default_label = $settings['appearance']['field-label'];
			self::$default_size  = $settings['appearance']['field-size'];
			self::$default_color = $settings['appearance']['field-color'];
		}

		/**
		 * Return the field title, for use in the form editor.
		 *
		 * @return string
		 */
		public function get_form_editor_field_title() {
			return esc_attr__( 'Clipchamp', 'clipchamp' );
		}

		/**
		 * Return the field title, for use in the form editor.
		 *
		 * @return string
		 */
		public function get_form_editor_field_label() {
			return esc_attr__( 'Video Upload', 'clipchamp' );
		}

		/**
		 * Assign the field button to the Advanced Fields group.
		 *
		 * @return array
		 */
		public function get_form_editor_button() {
			return array(
				'group' => 'advanced_fields',
				'text'  => $this->get_form_editor_field_label(),
			);
		}

		/**
		 * The settings which should be available on the field in the form editor.
		 *
		 * @return array
		 */
		public function get_form_editor_field_settings() {
			return array(
				'label_setting',
				'button_label_setting',
				'description_setting',
				'css_class_setting',
				'button_size_setting',
				'size_setting',
				'admin_label_setting',
				'conditional_logic_field_setting',
			);
		}

		/**
		 * Enable this field for use with conditional logic.
		 *
		 * @return bool
		 */
		public function is_conditional_logic_supported() {
			return true;
		}

		/**
		 * The scripts to be included in the form editor.
		 *
		 * @return string
		 */
		public function get_form_editor_inline_script_on_page_render() {
			// set the default field label for the clipchamp type field
			$script = sprintf( "function SetDefaultValues_clipchamp(field) {field.label = '%s';field.buttonLabel = '%s'; field.buttonSize = '%s';}", $this->get_form_editor_field_label(), self::$default_label, self::$default_size ) . PHP_EOL;
			// initialize the fields custom settings
			$script .= '
				jQuery( document ).bind( "gform_load_field_settings", function ( event, field, form ) {
					var buttonLabel = field.buttonLabel == undefined ? "" : field.buttonLabel;
					var buttonSize = field.buttonSize == undefined ? "" : field.buttonSize;
					jQuery( "#button_label_setting" ).val(buttonLabel);
					jQuery( "#button_size_setting" ).val(buttonSize);
				});
			';
			// saving the clipchamp settings
			$script .= '
				function SetButtonSizeSetting( input ) {
					var value = $( input ).val();
					SetFieldProperty( "buttonSize", value);
					$( input ).closest( "li.gfield" ).find( ".ginput_container_clipchamp .clipchamp-button" )
						.removeClass( "clipchamp-button-tiny clipchamp-button-small clipchamp-button-medium clipchamp-button-large" )
						.addClass( "clipchamp-button-" + value );
				}
			';
			$script .= '
				function SetButtonLabelSetting( input ) {
					var value = $( input ).val();
					SetFieldProperty( "buttonLabel", value);
					$( input ).closest( "li.gfield" ).find( ".ginput_container_clipchamp .clipchamp-button" ).val( value );
				}
			';
			return $script;
		}

		/**
		 * Format the entry value for display on the entries list page.
		 *
		 * Return a value that's safe to display on the page.
		 *
		 * @param string|array $value    The field value.
		 * @param array        $entry    The Entry Object currently being processed.
		 * @param string       $field_id The field or input ID currently being processed.
		 * @param array        $columns  The properties for the columns being displayed on the entry list page.
		 * @param array        $form     The Form Object currently being processed.
		 *
		 * @return string
		 */
		public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
			$link  = get_post_meta( $value, 'ccb_video-url', true );
			$title = get_the_title( $value );

			if ( empty( $link ) ) {
				$link = get_permalink( $value );
			}

			return wp_kses_post( sprintf( '<a href="%1$s">%2$s</a>', $link, $title ) );
		}

		/**
		 * Format the entry value for display on the entry detail page and for the {all_fields} merge tag.
		 *
		 * Return a value that's safe to display for the context of the given $format.
		 *
		 * @param string|array $value    The field value.
		 * @param string       $currency The entry currency code.
		 * @param bool|false   $use_text When processing choice based fields should the choice text be returned instead of the value.
		 * @param string       $format   The format requested for the location the merge is being used. Possible values: html, text or url.
		 * @param string       $media    The location where the value will be displayed. Possible values: screen or email.
		 *
		 * @return string
		 */
		public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
			$link  = get_post_meta( $value, 'ccb_video-url', true );
			$title = get_the_title( $value );

			if ( empty( $link ) ) {
				$link = get_permalink( $value );
			}
			return wp_kses_post( sprintf( '<a href="%1$s">%2$s</a>', $link, $title ) );
		}

		/**
		 * Define the fields inner markup.
		 *
		 * @param array        $form The Form Object currently being processed.
		 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
		 * @param null|array   $entry Null or the Entry Object currently being edited.
		 *
		 * @return string
		 */
		public function get_field_input( $form, $value = '', $entry = null ) {
			$id      = absint( $this->id );
			$form_id = absint( $form['id'] );

			if ( is_admin() ) {
				$is_entry_detail = $this->is_entry_detail();
				$is_form_editor  = $this->is_form_editor();

				// Prepare the value of the input ID attribute.
				$field_id = $is_entry_detail || $is_form_editor || 0 === $form_id ? "input_$id" : 'input_' . $form_id . "_$id";
				$value    = esc_attr( $value );

				// Get the value of the custom settings for the current field.
				$button_size  = $this->buttonSize; // ValidVariableName okay.
				$button_label = $this->buttonLabel;

				// Get the default color as css
				$default_color_css = 'background-color: ' . self::$default_color;

				// Prepare the input classes.
				$size         = $this->size;
				$class_suffix = $is_entry_detail ? '_admin' : '';
				$class        = $size . $class_suffix . ' clipchamp-button clipchamp-button-' . $button_size;

				// Prepare the other input attributes.
				$tabindex          = $this->get_tabindex();
				$logic_event       = ! $is_form_editor && ! $is_entry_detail ? $this->get_conditional_logic_event( 'keyup' ) : '';
				$invalid_attribute = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

				// Prepare the input tag for this field.
				$input = "<input name='input_{$id}' id='{$field_id}' type='button' value='{$button_label}' class='{$class}' style='{$default_color_css}' {$tabindex} {$logic_event} {$invalid_attribute} />";
			} else {
				$attributes = array(
					'label' => $this->buttonLabel,
					'size'  => $this->buttonSize,
				);
				$input      = CCB_Uploader::render( $attributes );
			}

			$field_id = 0 === $form_id ? "input_$id" : 'input_' . $form_id . "_$id";
			return sprintf( "<div class='ginput_container ginput_container_%s'><p class='clipchamp-button-complete' style='display: none;'><input type='text' readonly='readonly' /></p><input name='input_{$id}' id='{$field_id}' type='hidden' class='clipchamp-button-input' value='' />%s</div>", $this->type, $input );
		}
	}
}
