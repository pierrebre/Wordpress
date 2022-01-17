<?php
/**
 *  Carousel view
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/public/template
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="pcp_wrapper-<?php echo esc_attr( $pcp_gl_id ); ?>" class="<?php self::pcp_wrapper_classes( $layout_preset, $pcp_gl_id ); ?>">
<?php if ( $show_preloader ) { ?>
<div id="pcp-preloader" class="pcp-preloader"></div>
	<?php
}
if ( $view_options['section_title'] ) {
	do_action( 'pcp_before_section_title' );
	SP_PC_HTML::pcp_section_title( $pcp_gl_id );
	do_action( 'pcp_after_section_title' );
}
?>

	<div class="sp-pcp-row">
		<?php self::pcp_get_posts( $options, $layout_preset, $post_content_sorter, $pcp_query, $pcp_gl_id ); ?>
	</div>
	<?php require SP_PC_TEMPLATE_PATH . '/pagination.php'; ?>
</div>
