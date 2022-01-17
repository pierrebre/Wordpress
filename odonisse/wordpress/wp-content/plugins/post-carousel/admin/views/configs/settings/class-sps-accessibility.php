<?php
/**
 * The Accessibility setting configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * The accessibility setting class.
 */
class SPS_Accessibility {

	/**
	 * Accessibility setting section.
	 *
	 * @param string $prefix The settings.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'Accessibility', 'smart-post-show' ),
				'icon'   => 'fa fa-braille',
				'fields' => array(
					array(
						'id'         => 'accessibility',
						'type'       => 'switcher',
						'title'      => __( 'Carousel Accessibility', 'smart-post-show' ),
						'text_on'    => __( 'Enabled', 'smart-post-show' ),
						'text_off'   => __( 'Disabled', 'smart-post-show' ),
						'text_width' => 100,
						'default'    => true,
					),
					array(
						'id'         => 'prev_slide_message',
						'type'       => 'text',
						'title'      => __( 'Previous Slide Message', 'smart-post-show' ),
						'default'    => __( 'Previous slide', 'smart-post-show' ),
						'dependency' => array( 'accessibility', '==', 'true' ),
					),
					array(
						'id'         => 'next_slide_message',
						'type'       => 'text',
						'title'      => __( 'Next Slide Message', 'smart-post-show' ),
						'default'    => __( 'Next slide', 'smart-post-show' ),
						'dependency' => array( 'accessibility', '==', 'true' ),
					),
					array(
						'id'         => 'first_slide_message',
						'type'       => 'text',
						'title'      => __( 'First Slide Message', 'smart-post-show' ),
						'default'    => __( 'This is the first slide', 'smart-post-show' ),
						'dependency' => array( 'accessibility', '==', 'true' ),
					),
					array(
						'id'         => 'last_slide_message',
						'type'       => 'text',
						'title'      => __( 'Last Slide Message', 'smart-post-show' ),
						'default'    => __( 'This is the last slide', 'smart-post-show' ),
						'dependency' => array( 'accessibility', '==', 'true' ),
					),
					array(
						'id'         => 'pagination_bullet_message',
						'type'       => 'text',
						'title'      => __( 'Pagination Bullet Message', 'smart-post-show' ),
						'default'    => __( 'Go to slide {{index}}', 'smart-post-show' ),
						'dependency' => array( 'accessibility', '==', 'true' ),
					),
				),
			)
		);
	}
}
