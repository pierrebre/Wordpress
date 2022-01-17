<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: accordion
 *
 * @since 2.0
 * @version 2.0
 */
if ( ! class_exists( 'SP_PC_Field_accordion' ) ) {
	class SP_PC_Field_accordion extends SP_PC_Fields {


		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {

			$unallows = array( 'accordion' );

			echo $this->field_before();

			echo '<div class="spf-accordion-items">';

			foreach ( $this->field['accordions'] as $key => $accordion ) {

				echo '<div class="spf-accordion-item">';

				$icon = ( ! empty( $accordion['icon'] ) ) ? 'spf--icon ' . $accordion['icon'] : 'spf-accordion-icon fa fa-angle-right';

				echo '<h4 class="spf-accordion-title">';
				echo '<i class="' . $icon . '"></i>';
					echo $accordion['title'];
					echo '</h4>';

				echo '<div class="spf-accordion-content">';

				foreach ( $accordion['fields'] as $field ) {

					if ( in_array( $field['type'], $unallows ) ) {
						$field['_notice'] = true;
					}

					$field_id      = ( isset( $field['id'] ) ) ? $field['id'] : '';
					$field_default = ( isset( $field['default'] ) ) ? $field['default'] : '';
					$field_value   = ( isset( $this->value[ $field_id ] ) ) ? $this->value[ $field_id ] : $field_default;
					$unique_id     = ( ! empty( $this->unique ) ) ? $this->unique . '[' . $this->field['id'] . ']' : $this->field['id'];

					SP_PC::field( $field, $field_value, $unique_id, 'field/accordion' );

				}

				echo '</div>';

				echo '</div>';

			}

			echo '</div>';

			echo $this->field_after();

		}

	}
}
