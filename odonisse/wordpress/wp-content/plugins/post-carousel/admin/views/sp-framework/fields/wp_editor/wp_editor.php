<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: wp_editor
 *
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! class_exists( 'SP_PC_Field_wp_editor' ) ) {
	class SP_PC_Field_wp_editor extends SP_PC_Fields {


		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			  parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {

			$args = wp_parse_args(
				$this->field,
				array(
					'tinymce'       => true,
					'quicktags'     => true,
					'media_buttons' => true,
					'height'        => '',
				)
			);

			$attributes = array(
				'rows'         => 10,
				'class'        => 'wp-editor-area',
				'autocomplete' => 'off',
			);

			$editor_height = ( ! empty( $args['height'] ) ) ? ' style="height:' . $args['height'] . ';"' : '';

			$editor_settings = array(
				'tinymce'       => $args['tinymce'],
				'quicktags'     => $args['quicktags'],
				'media_buttons' => $args['media_buttons'],
			);

			echo $this->field_before();

			echo ( spf_wp_editor_api() ) ? '<div class="spf-wp-editor" data-editor-settings="' . esc_attr( wp_json_encode( $editor_settings ) ) . '">' : '';

			echo '<textarea name="' . $this->field_name() . '"' . $this->field_attributes( $attributes ) . $editor_height . '>' . $this->value . '</textarea>';

			echo '<div class="clear"></div>';

			echo ( spf_wp_editor_api() ) ? '</div>' : '';

			echo $this->field_after();

		}

		public function enqueue() {

			if ( spf_wp_editor_api() && function_exists( 'wp_enqueue_editor' ) ) {

				 wp_enqueue_editor();

				  $this->setup_wp_editor_settings();

				 add_action( 'print_default_editor_scripts', array( &$this, 'setup_wp_editor_media_buttons' ) );

			}

		}

		// Setup wp editor media buttons
		public function setup_wp_editor_media_buttons() {

			   ob_start();
			echo '<div class="wp-media-buttons">';
			 do_action( 'media_buttons' );
			  echo '</div>';
			 $media_buttons = ob_get_clean();

			  echo '<script type="text/javascript">';
			echo 'var spf_media_buttons = ' . wp_json_encode( $media_buttons ) . ';';
			  echo '</script>';

		}

		// Setup wp editor settings
		public function setup_wp_editor_settings() {

			if ( spf_wp_editor_api() && class_exists( '_WP_Editors' ) ) {

				$defaults = apply_filters(
					'spf_wp_editor',
					array(
						'tinymce' => array(
							'wp_skip_init' => true,
						),
					)
				);

				$setup = _WP_Editors::parse_settings( 'spf_wp_editor', $defaults );

				_WP_Editors::editor_settings( 'spf_wp_editor', $setup );

			}

		}

	}
}
