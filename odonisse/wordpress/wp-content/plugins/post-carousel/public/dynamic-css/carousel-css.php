<?php
/**
 * The dynamic CSS for Carousel layout.
 *
 * @package Smart_Post_Show
 */

$carousel_nav_position = SP_PC_Functions::pcp_metabox_value( 'pcp_carousel_nav_position', $view_options );
if ( 'vertically_center_outer' === $carousel_nav_position ) {
	$custom_css .= '.pcp-wrapper-{$pcp_id} .swiper-container{ position: static; }';
}

// Pagination options.
$pcp_pagination          = isset( $view_options['pcp_pagination'] ) ? $view_options['pcp_pagination'] : '';
$_pagination_color_set   = $view_options['pcp_pagination_color_set'];
$_pagination_colors      = $_pagination_color_set['pcp_pagination_color'];
$pagination_color        = $_pagination_colors['color'];
$pagination_color_active = $_pagination_colors['active-color'];
if ( 'hide_on_mobile' === $pcp_pagination ) {
	$custom_css .= "@media (max-width: 480px) { #pcp_wrapper-{$pcp_id} .pcp-pagination{ display: none; } }";
} $custom_css .= "#pcp_wrapper-{$pcp_id} .dots .swiper-pagination-bullet{ background: {$pagination_color}; } #pcp_wrapper-{$pcp_id} .dots .swiper-pagination-bullet-active { background: {$pagination_color_active}; }";

$carousel_nav_position = SP_PC_Functions::pcp_metabox_value( 'pcp_carousel_nav_position', $view_options );
if ( 'vertically_center_outer' === $carousel_nav_position ) {
	$custom_css .= '.pcp-wrapper-{$pcp_id} .swiper-container{ position: static; }';
}
// Navigation options.
$pcp_navigation         = isset( $view_options['pcp_navigation'] ) ? $view_options['pcp_navigation'] : '';
$_nav_colors            = SP_PC_Functions::pcp_metabox_value( 'pcp_nav_colors', $view_options );
$nav_color              = SP_PC_Functions::pcp_metabox_value( 'color', $_nav_colors );
$nav_color_hover        = SP_PC_Functions::pcp_metabox_value( 'hover-color', $_nav_colors );
$nav_color_bg           = SP_PC_Functions::pcp_metabox_value( 'bg', $_nav_colors );
$nav_color_bg_hover     = SP_PC_Functions::pcp_metabox_value( 'hover-bg', $_nav_colors );
$nav_color_border       = SP_PC_Functions::pcp_metabox_value( 'border-color', $_nav_colors );
$nav_color_border_hover = SP_PC_Functions::pcp_metabox_value( 'hover-border-color', $_nav_colors );
$nav_icon_size          = SP_PC_Functions::pcp_metabox_value( 'pcp_nav_icon_size', $view_options );
$_nav_icon_radius       = SP_PC_Functions::pcp_metabox_value( 'navigation_icons_border_radius', $view_options );
if ( 'hide_on_mobile' === $pcp_navigation ) {
	$custom_css .= "@media (max-width: 480px) { #pcp_wrapper-{$pcp_id} .pcp-button-prev, #pcp_wrapper-{$pcp_id} .pcp-button-next{ display: none; } }";
} $custom_css .= "#pcp_wrapper-{$pcp_id} .pcp-button-prev, #pcp_wrapper-{$pcp_id} .pcp-button-next{ background-image: none; background-size: auto; background-color: {$nav_color_bg}; font-size: {$nav_icon_size}px; height: 33px; width: 33px; margin-top: 8px; border: 1px solid {$nav_color_border}; text-align: center; line-height: 30px; -webkit-transition: 0.3s; border-radius: {$_nav_icon_radius['all']}{$_nav_icon_radius['unit']}; }";
$custom_css   .= "#pcp_wrapper-{$pcp_id} .pcp-button-prev:hover, #pcp_wrapper-{$pcp_id} .pcp-button-next:hover{ background-color: {$nav_color_bg_hover}; border-color: {$nav_color_border_hover}; } #pcp_wrapper-{$pcp_id} .pcp-button-prev .fa, #pcp_wrapper-{$pcp_id} .pcp-button-next .fa { color: {$nav_color}; } #pcp_wrapper-{$pcp_id} .pcp-button-prev:hover .fa, #pcp_wrapper-{$pcp_id} .pcp-button-next:hover .fa { color: {$nav_color_hover}; } #pcp_wrapper-{$pcp_id}.pcp-carousel-wrapper .sp-pcp-post{ margin-top: 0; }";
