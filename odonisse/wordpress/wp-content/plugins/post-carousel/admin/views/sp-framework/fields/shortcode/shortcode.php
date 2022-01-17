<?php if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.
/**
 *
 * Field: shortcode
 *
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! class_exists( 'SP_PC_Field_shortcode' ) ) {
	class SP_PC_Field_shortcode extends SP_PC_Fields {

		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
			parent::__construct( $field, $value, $unique, $where, $parent );
		}
		public function render() {
			// Get the Post ID.
			$post_id = get_the_ID();
			echo ( ! empty( $post_id ) ) ? '<div class="pcp-scode-wrap-side"><p>To display your show or view, add the following shortcode into your post, custom post types, page, widget or block editor. If adding the show to your theme files, additionally include the surrounding PHP code, <a href="https://docs.shapedplugin.com/docs/post-carousel/create-your-first-post-show/add-new-post-show/" target="_blank">see how</a>.‎</p><span class="pcp-shortcode-selectable">[smart_post_show id="' . $post_id . '"]</span></div><div class="pcp-after-copy-text"><i class="fa fa-check-circle"></i> Shortcode Copied to Clipboard! </div>' : '';
		}

	}
}
