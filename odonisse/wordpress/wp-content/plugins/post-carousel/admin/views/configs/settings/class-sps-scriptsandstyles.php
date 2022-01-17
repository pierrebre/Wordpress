<?php
/**
 * The Enqueue and Dequeue CSS and JS files setting configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * The Layout building class.
 */
class SPS_ScriptsAndStyles {

	/**
	 * Advanced setting section.
	 *
	 * @param string $prefix The settings.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'Scripts & Styles', 'smart-post-show' ),
				'icon'   => 'fa fa-file-code-o',
				'fields' => array(
					array(
						'type'    => 'subheading',
						'content' => __( 'Enqueue or Dequeue JS', 'smart-post-show' ),
					),
					array(
						'id'         => 'pcp_swiper_js',
						'type'       => 'switcher',
						'title'      => __( 'Swiper JS', 'smart-post-show' ),
						'text_on'    => __( 'Enqueued', 'smart-post-show' ),
						'text_off'   => __( 'Dequeued', 'smart-post-show' ),
						'text_width' => 95,
						'default'    => true,
					),
					array(
						'type'    => 'subheading',
						'content' => __( 'Enqueue or Dequeue CSS', 'smart-post-show' ),
					),
					array(
						'id'         => 'pcp_swiper_css',
						'type'       => 'switcher',
						'title'      => __( 'Swiper CSS', 'smart-post-show' ),
						'text_on'    => __( 'Enqueued', 'smart-post-show' ),
						'text_off'   => __( 'Dequeued', 'smart-post-show' ),
						'text_width' => 95,
						'default'    => true,
					),
					array(
						'id'         => 'pcp_fontawesome_css',
						'type'       => 'switcher',
						'title'      => __( 'Font Awesome CSS', 'smart-post-show' ),
						'text_on'    => __( 'Enqueued', 'smart-post-show' ),
						'text_off'   => __( 'Dequeued', 'smart-post-show' ),
						'text_width' => 95,
						'default'    => true,
					),
				),
			)
		);
	}
}
