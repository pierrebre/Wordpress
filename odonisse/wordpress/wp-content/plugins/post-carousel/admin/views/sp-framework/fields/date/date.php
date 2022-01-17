<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: date
 *
 * @since 2.0
 * @version 2.0
 */
if ( ! class_exists( 'SP_PC_Field_date' ) ) {
	class SP_PC_Field_date extends SP_PC_Fields {


		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			 parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {

			$default_settings = array(
				'dateFormat' => 'mm/dd/yy',
			);

			$view_options = ( ! empty( $this->field['settings'] ) ) ? $this->field['settings'] : array();
			$view_options = wp_parse_args( $view_options, $default_settings );

			echo $this->field_before();

			if ( ! empty( $this->field['from_to'] ) ) {

				$args = wp_parse_args(
					$this->field,
					array(
						'text_from' => 'From',
						'text_to'   => 'To',
					)
				);

				$value = wp_parse_args(
					$this->value,
					array(
						'from' => '',
						'to'   => '',
					)
				);

				echo '<label class="spf--from">' . $args['text_from'] . ' <input type="text" name="' . $this->field_name( '[from]' ) . '" value="' . $value['from'] . '"' . $this->field_attributes() . '/></label>';
				echo '<label class="spf--to">' . $args['text_to'] . ' <input type="text" name="' . $this->field_name( '[to]' ) . '" value="' . $value['to'] . '"' . $this->field_attributes() . '/></label>';

			} else {

				echo '<input type="text" name="' . $this->field_name() . '" value="' . $this->value . '"' . $this->field_attributes() . '/>';

			}

			echo '<div class="spf-date-settings" data-settings="' . esc_attr( json_encode( $view_options ) ) . '"></div>';

			echo $this->field_after();

		}

		public function enqueue() {

			if ( ! wp_script_is( 'jquery-ui-datepicker' ) ) {
				wp_enqueue_script( 'jquery-ui-datepicker' );
			}

		}

	}
}
