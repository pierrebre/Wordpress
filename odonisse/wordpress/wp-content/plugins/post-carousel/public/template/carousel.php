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
$carousel_autoplay = ( isset( $view_options['pcp_autoplay'] ) && ( $view_options['pcp_autoplay'] ) ) ? 'true' : 'false';
$autoplay_speed    = isset( $view_options['pcp_autoplay_speed'] ) ? $view_options['pcp_autoplay_speed'] : '';
$carousel_speed    = isset( $view_options['pcp_carousel_speed'] ) ? $view_options['pcp_carousel_speed'] : '';
$pause_hover       = ( isset( $view_options['pcp_pause_hover'] ) && ( $view_options['pcp_pause_hover'] ) ) ? 'true' : 'false';

$infinite_loop        = ( isset( $view_options['pcp_infinite_loop'] ) && ( $view_options['pcp_infinite_loop'] ) ) ? 'true' : 'false';
$carousel_auto_height = ( isset( $view_options['pcp_adaptive_height'] ) && ( $view_options['pcp_adaptive_height'] ) ) ? 'true' : 'false';
$number_of_columns    = isset( $view_options['pcp_number_of_columns'] ) ? $view_options['pcp_number_of_columns'] : '';
$lazy_load            = ( isset( $view_options['pcp_lazy_load'] ) && ( $view_options['pcp_lazy_load'] ) ) ? 'true' : 'false';
// Direction.
$carousel_direction                   = ( isset( $view_options['pcp_carousel_direction'] ) ) ? $view_options['pcp_carousel_direction'] : '';
$is_carousel_accessibility            = ( isset( $pcp_settings['accessibility'] ) && ( $pcp_settings['accessibility'] ) ) ? 'true' : 'false';
$accessibility_prev_slide_text        = isset( $pcp_settings['prev_slide_message'] ) ? $pcp_settings['prev_slide_message'] : '';
$accessibility_next_slide_text        = isset( $pcp_settings['next_slide_message'] ) ? $pcp_settings['next_slide_message'] : '';
$accessibility_first_slide_text       = isset( $pcp_settings['first_slide_message'] ) ? $pcp_settings['first_slide_message'] : '';
$accessibility_last_slide_text        = isset( $pcp_settings['last_slide_message'] ) ? $pcp_settings['last_slide_message'] : '';
$accessibility_pagination_bullet_text = isset( $pcp_settings['pagination_bullet_message'] ) ? $pcp_settings['pagination_bullet_message'] : '';
if ( $pcp_settings['pcp_swiper_js'] ) {
	wp_enqueue_script( 'pcp_swiper' );
}
// Navigation.
$_navigation = isset( $view_options['pcp_navigation'] ) ? $view_options['pcp_navigation'] : '';
switch ( $_navigation ) {
	case 'show':
		$navigation        = 'true';
		$navigation_mobile = 'true';
		break;
	case 'hide':
		$navigation        = 'false';
		$navigation_mobile = 'false';
		break;
	case 'hide_on_mobile':
		$navigation        = 'true';
		$navigation_mobile = 'false';
		break;
}

// Pagination Settings.
$_pagination = isset( $view_options['pcp_pagination'] ) ? $view_options['pcp_pagination'] : '';
switch ( $_pagination ) {
	case 'show':
		$pagination        = 'true';
		$pagination_mobile = 'true';
		break;
	case 'hide':
		$pagination        = 'false';
		$pagination_mobile = 'false';
		break;
	case 'hide_on_mobile':
		$pagination        = 'true';
		$pagination_mobile = 'false';
		break;
}
$pcp_accessibility  = ( isset( $view_options['pcp_accessibility'] ) && ( $view_options['pcp_accessibility'] ) ) ? 'true' : 'false';
$touch_swipe        = ( isset( $view_options['touch_swipe'] ) && ( $view_options['touch_swipe'] ) ) ? 'true' : 'false';
$slider_draggable   = ( isset( $view_options['slider_draggable'] ) && ( $view_options['slider_draggable'] ) ) ? 'true' : 'false';
$slider_mouse_wheel = ( isset( $view_options['slider_mouse_wheel'] ) && ( $view_options['slider_mouse_wheel'] ) ) ? 'true' : 'false';


?>
<!-- Markup Starts -->
<div id="pcp_wrapper-<?php echo esc_html( $pcp_gl_id ); ?>" class="<?php self::pcp_wrapper_classes( $layout_preset, $pcp_gl_id ); ?> standard" data-sid="<?php echo esc_html( $pcp_gl_id ); ?>">
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
	<div id="sp-pcp-id-<?php echo esc_html( $pcp_gl_id ); ?>" class="swiper-container sp-pcp-carousel top_right" dir="<?php echo esc_html( $carousel_direction ); ?>" data-carousel='{ "speed":<?php echo esc_html( $carousel_speed ); ?>, "items":<?php echo esc_html( $number_of_columns['lg_desktop'] ); ?>, "spaceBetween":<?php echo esc_html( $margin_between_post ); ?>, "navigation":<?php echo esc_html( $navigation ); ?>, "pagination": <?php echo esc_html( $pagination ); ?>, "autoplay": <?php echo esc_html( $carousel_autoplay ); ?>, "autoplay_speed": <?php echo esc_html( $autoplay_speed ); ?>, "loop": <?php echo esc_html( $infinite_loop ); ?>, "autoHeight": <?php echo esc_html( $carousel_auto_height ); ?>, "lazy":  <?php echo esc_html( $lazy_load ); ?>, "simulateTouch": <?php echo esc_html( $slider_draggable ); ?>, "slider_mouse_wheel": <?php echo esc_html( $slider_mouse_wheel ); ?>,"allowTouchMove": <?php echo esc_html( $touch_swipe ); ?>, "slidesPerView": {"lg_desktop": <?php echo esc_html( $number_of_columns['lg_desktop'] ); ?>, "desktop": <?php echo esc_html( $number_of_columns['desktop'] ); ?>, "tablet": <?php echo esc_html( $number_of_columns['tablet'] ); ?>, "mobile_landscape": <?php echo esc_html( $number_of_columns['mobile_landscape'] ); ?>, "mobile": <?php echo esc_html( $number_of_columns['mobile'] ); ?>}, "navigation_mobile": <?php echo esc_html( $navigation_mobile ); ?>, "pagination_mobile": <?php echo esc_html( $pagination_mobile ); ?>, "stop_onHover": <?php echo esc_html( $pause_hover ); ?>, "enabled": <?php echo esc_html( $is_carousel_accessibility ); ?>, "prevSlideMessage": "<?php echo esc_html( $accessibility_prev_slide_text ); ?>", "nextSlideMessage": "<?php echo esc_html( $accessibility_next_slide_text ); ?>", "firstSlideMessage": "<?php echo esc_html( $accessibility_first_slide_text ); ?>", "lastSlideMessage": "<?php echo esc_html( $accessibility_last_slide_text ); ?>", "paginationBulletMessage": "<?php echo esc_html( $accessibility_pagination_bullet_text ); ?>" }'>
			<div class="swiper-wrapper">
				<?php self::pcp_get_posts( $options, $layout_preset, $post_content_sorter, $pcp_query, $pcp_gl_id ); ?>
			</div>
			<?php
			if ( 'true' === $pagination ) {
				?>
			<div class="pcp-pagination swiper-pagination dots"></div>
			<?php } ?>
			<?php if ( 'true' === $navigation ) { ?>
				<div class="pcp-button-next swiper-button-next top_right"><i class="fa fa-angle-right"></i></div>
				<div class="pcp-button-prev swiper-button-prev top_right"><i class="fa fa-angle-left"></i></div><?php } ?>
	</div>
</div>
