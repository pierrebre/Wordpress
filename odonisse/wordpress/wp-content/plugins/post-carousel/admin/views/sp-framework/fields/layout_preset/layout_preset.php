<?php if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.
/**
 *
 * Field: layout_preset
 *
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! class_exists( 'SP_PC_Field_layout_preset' ) ) {
	class SP_PC_Field_layout_preset extends SP_PC_Fields {

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

				echo '<div class="spf-siblings spf--image-group" data-multiple="' . $args['multiple'] . '">';

				$num = 1;

				foreach ( $args['options'] as $key => $option ) {

					$type               = ( $args['multiple'] ) ? 'checkbox' : 'radio';
					$extra              = ( $args['multiple'] ) ? '[]' : '';
					$active             = ( in_array( $key, $value ) ) ? ' spf--active' : '';
					$checked            = ( in_array( $key, $value ) ) ? ' checked' : '';
					$pcp_pro_only_class = isset( $option['pro_only'] ) ? ' pcp-pro-only' : '';

					echo '<div class="spf--sibling spf--image' . $active . $pcp_pro_only_class . '">';
					echo '<img src="' . esc_url( $option['image'] ) . '" alt="' . $option['text'] . '" />';
					echo '<input type="' . $type . '" name="' . esc_attr( $this->field_name( $extra ) ) . '" value="' . $key . '"' . $this->field_attributes() . $checked . '/>';
					echo '<span class="sp-carousel-type">' . esc_html( $option['text'] ) . '</span>';
					echo '</div>';

				}

				echo '</div>';

			}

			echo '<div class="clear"></div>';

			echo $this->field_after();

		}

		public function output() {

			$output    = '';
			$bg_image  = array();
			$important = ( ! empty( $this->field['output_important'] ) ) ? '!important' : '';
			$elements  = ( is_array( $this->field['output'] ) ) ? join( ',', $this->field['output'] ) : $this->field['output'];

			if ( ! empty( $elements ) && isset( $this->value ) && $this->value !== '' ) {
				$output = $elements . '{background-image:url(' . $this->value . ')' . $important . ';}';
			}

			$this->parent->output_css .= $output;

			return $output;

		}

	}
}
