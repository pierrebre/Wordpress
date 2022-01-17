<?php if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.
/**
 *
 * Field: notice
 *
 * @since 2.0
 * @version 2.0
 */
if ( ! class_exists( 'SP_PC_Field_notice' ) ) {
	class SP_PC_Field_notice extends SP_PC_Fields {


		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {

			parent::__construct( $field, $value, $unique, $where, $parent );
		}

		public function render() {
			echo $this->field_before();
			$style = ( ! empty( $this->field['style'] ) ) ? $this->field['style'] : 'normal';

			echo ( ! empty( $this->field['content'] ) ) ? '<div class="spf-notice spf-notice-' . $style . '">' . $this->field['content'] . '</div>' : '';
			echo $this->field_after();
		}

	}
}
