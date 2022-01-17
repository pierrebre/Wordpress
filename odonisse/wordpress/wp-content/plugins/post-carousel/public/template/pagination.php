<?php
/**
 *  Pagination view
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Paged argument.
if ( get_query_var( 'paged' ) ) {
	$pcp_paged = get_query_var( 'paged' );
} elseif ( get_query_var( 'page' ) ) {
	$pcp_paged = get_query_var( 'page' );
} else {
	$pcp_paged = 1;
}
if ( $show_pagination ) {
	?>
	<span class="sp-pcp-pagination-data" style="display:none;" data-loadmoretext="<?php echo esc_attr( $view_options['load_more_button_text'] ); ?>" data-endingtext="<?php echo esc_attr( $view_options['load_more_ending_message'] ); ?>"></span>

		<nav class="pcp-post-pagination pcp-on-desktop <?php echo esc_attr( $pagination_type ); ?>">
			<?php SP_PC_HTML::pcp_pagination_bar( $pcp_query, $pcp_gl_id, $pcp_paged ); ?>
		</nav>
		<?php if ( 'filter_layout' !== $layout_preset ) { ?>
			<nav class="pcp-post-pagination pcp-on-mobile <?php echo esc_attr( $pagination_type_mobile ); ?>">
			<?php SP_PC_HTML::pcp_pagination_bar( $pcp_query, $pcp_gl_id, $pcp_paged, 'on_mobile' ); ?>
		</nav>
			<?php
}
?>
	<?php
}
