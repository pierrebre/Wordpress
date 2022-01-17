<?php
/**
 * The Carousel section Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

	/**
	 * The Carousel building class.
	 */
class SPS_Carousel {

	/**
	 * Carousel section metabox.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'Carousel Controls', 'smart-post-show' ),
				'icon'   => 'fa fa-sliders',
				'fields' => array(
					array(
						'id'       => 'pcp_carousel_mode',
						'type'     => 'button_set',
						'title'    => __( 'Carousel Mode', 'smart-post-show' ),
						'subtitle' => __( 'Choose a mode for the carousel.', 'smart-post-show' ),
						'options'  => array(
							'standard' => __( 'Standard', 'smart-post-show' ),
							'center'   => array(
								'text'     => __( 'Center', 'smart-post-show' ),
								'pro_only' => true,
							),
							'ticker'   => array(
								'text'     => __( 'Ticker', 'smart-post-show' ),
								'pro_only' => true,
							),
						),
						'default'  => 'standard',
					),
					array(
						'id'       => 'pcp_carousel_speed',
						'type'     => 'spinner',
						'title'    => __( 'Carousel Speed', 'smart-post-show' ),
						'subtitle' => __( 'Set carousel speed in millisecond.', 'smart-post-show' ),
						'default'  => '600',
						'min'      => 0,
						'max'      => 20000,
						'step'     => 100,
						'unit'     => 'ms',
					),
					array(
						'id'       => 'pcp_autoplay',
						'type'     => 'switcher',
						'title'    => __( 'AutoPlay', 'smart-post-show' ),
						'subtitle' => __( 'On/Off carousel autoplay.', 'smart-post-show' ),
						'default'  => true,
					),
					array(
						'id'       => 'pcp_autoplay_speed',
						'type'     => 'spinner',
						'title'    => __( 'AutoPlay Speed', 'smart-post-show' ),
						'subtitle' => __( 'Set autoplay speed in millisecond.', 'smart-post-show' ),
						'default'  => '2000',
						'min'      => 0,
						'max'      => 10000,
						'step'     => 100,
						'unit'     => 'ms',
					),
					array(
						'id'       => 'pcp_pause_hover',
						'type'     => 'switcher',
						'title'    => __( 'Pause on Hover', 'smart-post-show' ),
						'subtitle' => __( 'On/Off carousel stop on hover.', 'smart-post-show' ),
						'default'  => true,
					),
					array(
						'id'       => 'pcp_infinite_loop',
						'type'     => 'switcher',
						'title'    => __( 'Infinite Loop', 'smart-post-show' ),
						'subtitle' => __( 'On/Off carousel infinite loop.', 'smart-post-show' ),
						'default'  => true,
					),
					array(
						'id'       => 'pcp_lazy_load',
						'type'     => 'switcher',
						'title'    => __( 'Lazy Load', 'smart-post-show' ),
						'subtitle' => __( 'On/Off lazy load.', 'smart-post-show' ),
						'default'  => true,
					),
					array(
						'id'       => 'pcp_carousel_direction',
						'type'     => 'button_set',
						'title'    => __( 'Carousel Direction', 'smart-post-show' ),
						'subtitle' => __( 'Choose a carousel direction.', 'smart-post-show' ),
						'options'  => array(
							'ltr' => __( 'Right to Left', 'smart-post-show' ),
							'rtl' => __( 'Left to Right', 'smart-post-show' ),
						),
						'default'  => 'ltr',
					),
					array(
						'id'         => 'pcp_slide_effect',
						'type'       => 'select',
						'title'      => __( 'Transition Effect', 'smart-post-show-pro' ),
						'subtitle'   => __( 'Select a slide transition effect.', 'smart-post-show-pro' ),
						'help'       => __( 'Fade, cube, and flip transition effects work only for the single column view.', 'smart-post-show-pro' ),
						'options'    => array(
							'slide'     => __( 'Slide', 'smart-post-show-pro' ),
							'fade'      => __( 'Fade (Pro)', 'smart-post-show-pro' ),
							'coverflow' => __( 'Coverflow (Pro)', 'smart-post-show-pro' ),
							'cube'      => __( 'Cube (Pro)', 'smart-post-show-pro' ),
							'flip'      => __( 'Flip (Pro)', 'smart-post-show-pro' ),
						),
						'default'    => 'slide',
					),
					array(
						'type'    => 'subheading',
						'content' => __( 'Navigation', 'smart-post-show' ),
					),

					// Navigation Settings.
					array(
						'id'       => 'pcp_navigation',
						'type'     => 'button_set',
						'title'    => __( 'Navigation', 'smart-post-show' ),
						'subtitle' => __( 'Show/Hide carousel navigation.', 'smart-post-show' ),
						'options'  => array(
							'show'           => __( 'Show', 'smart-post-show' ),
							'hide'           => __( 'Hide', 'smart-post-show' ),
							'hide_on_mobile' => __( 'Hide on Mobile', 'smart-post-show' ),
						),
						'default'  => 'show',
					),
					array(
						'id'         => 'pcp_nav_colors',
						'type'       => 'color_group',
						'title'      => __( 'Navigation Color', 'smart-post-show' ),
						'subtitle'   => __( 'Set color for the carousel navigation.', 'smart-post-show' ),
						'options'    => array(
							'color'              => __( 'Color', 'smart-post-show' ),
							'hover-color'        => __( 'Hover Color', 'smart-post-show' ),
							'bg'                 => __( 'Background', 'smart-post-show' ),
							'hover-bg'           => __( 'Hover Background', 'smart-post-show' ),
							'border-color'       => __( 'Border', 'smart-post-show' ),
							'hover-border-color' => __( 'Hover Border', 'smart-post-show' ),
						),
						'default'    => array(
							'color'              => '#aaa',
							'hover-color'        => '#fff',
							'bg'                 => '#fff',
							'hover-bg'           => '#e1624b',
							'border-color'       => '#aaa',
							'hover-border-color' => '#e1624b',
						),
						'dependency' => array( 'pcp_navigation', '!=', 'hide' ),
					),

					// Pagination Settings.
					array(
						'type'    => 'subheading',
						'content' => __( 'Pagination', 'smart-post-show' ),
					),
					array(
						'id'       => 'pcp_pagination',
						'type'     => 'button_set',
						'title'    => __( 'Pagination', 'smart-post-show' ),
						'subtitle' => __( 'Show/Hide carousel pagination.', 'smart-post-show' ),
						'options'  => array(
							'show'           => __( 'Show', 'smart-post-show' ),
							'hide'           => __( 'Hide', 'smart-post-show' ),
							'hide_on_mobile' => __( 'Hide on Mobile', 'smart-post-show' ),
						),
						'default'  => 'show',
					),
					array(
						'id'         => 'pcp_pagination_color_set',
						'type'       => 'fieldset',
						'class'      => 'pcp-pagination-color-set',
						'title'      => __( 'Pagination Color', 'smart-post-show' ),
						'subtitle'   => __( 'Set color for the carousel pagination.', 'smart-post-show' ),
						'fields'     => array(
							array(
								'id'      => 'pcp_pagination_color',
								'type'    => 'color_group',
								'options' => array(
									'color'        => __( 'Color', 'smart-post-show' ),
									'active-color' => __( 'Active Color', 'smart-post-show' ),
								),
								'default' => array(
									'color'        => '#cccccc',
									'active-color' => '#e1624b',
								),
							),
						),
						'dependency' => array( 'pcp_pagination', '!=', 'hide' ),
					),

					// Miscellaneous Settings.
					array(
						'type'    => 'subheading',
						'content' => __( 'Miscellaneous', 'smart-post-show' ),
					),
					array(
						'id'       => 'pcp_adaptive_height',
						'type'     => 'switcher',
						'title'    => __( 'Adaptive Carousel Height', 'smart-post-show' ),
						'subtitle' => __( 'Dynamically adjust post carousel height based on each slide\'s height.', 'smart-post-show' ),
						'default'  => false,
					),
					array(
						'id'       => 'pcp_accessibility',
						'type'     => 'switcher',
						'title'    => __( 'Tab and Key Navigation', 'smart-post-show' ),
						'subtitle' => __( 'Enable/Disable carousel scroll with tab and keyboard.', 'smart-post-show' ),
						'default'  => true,
					),
					array(
						'id'       => 'touch_swipe',
						'type'     => 'switcher',
						'title'    => __( 'Touch Swipe', 'smart-post-show' ),
						'subtitle' => __( 'On/Off touch swipe mode.', 'smart-post-show' ),
						'default'  => true,
					),
					array(
						'id'       => 'slider_draggable',
						'type'     => 'switcher',
						'title'    => __( 'Mouse Draggable', 'smart-post-show' ),
						'subtitle' => __( 'On/Off mouse draggable mode.', 'smart-post-show' ),
						'default'  => true,
					),
					array(
						'id'       => 'slider_mouse_wheel',
						'type'     => 'switcher',
						'title'    => __( 'Mousewheel Control', 'smart-post-show' ),
						'subtitle' => __( 'On/Off mousewheel control.', 'smart-post-show' ),
						'default'  => false,
					),
				), // End of fields array.
			)
		); // Carousel Controls section end.
	}
}
