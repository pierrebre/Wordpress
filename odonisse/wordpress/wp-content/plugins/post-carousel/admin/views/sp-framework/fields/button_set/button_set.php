<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: button_set
 *
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! class_exists( 'SP_PC_Field_button_set' ) ) {
	class SP_PC_Field_button_set extends SP_PC_Fields {


		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {

			$args = wp_parse_args(
				$this->field,
				array(
					'multiple' => false,
					'options'  => array(),
				)
			);

			$value = ( is_array( $this->value ) ) ? $this->value : array_filter( (array) $this->value );

			echo $this->field_before();

			if ( ! empty( $args['options'] ) ) {

				echo '<div class="spf-siblings spf--button-group" data-multiple="' . $args['multiple'] . '">';

				foreach ( $args['options'] as $key => $option ) {

					$type               = ( $args['multiple'] ) ? 'checkbox' : 'radio';
					$extra              = ( $args['multiple'] ) ? '[]' : '';
					$active             = ( in_array( $key, $value ) || ( empty( $value ) && empty( $key ) ) ) ? ' spf--active' : '';
					$checked            = ( in_array( $key, $value ) ) ? ' checked' : '';
					$checked            = ( in_array( $key, $value ) || ( empty( $value ) && empty( $key ) ) ) ? ' checked' : '';
					$pcp_pro_only_class = isset( $option['pro_only'] ) ? ' pcp-pro-only' : '';
					echo '<div class="spf--sibling spf--button' . $active . $pcp_pro_only_class . '">';
					echo '<input type="' . $type . '" name="' . $this->field_name( $extra ) . '" value="' . $key . '"' . $this->field_attributes() . $checked . '/>';
					if ( isset( $option['text'] ) && ! empty( $option['text'] ) ) {
						echo $option['text'];
					} else {
						echo $option;
					}

					echo '</div>';

				}

				echo '</div>';

			}
			echo $this->field_after();
			echo '<div class="clear"></div>';

		}

	}
}
