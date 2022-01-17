<?php
/**
 * The Popup Settings Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * The Popup settings class.
 */
class SPS_DetailSettings {

	/**
	 * Popup settings section metabox.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'Detail page Settings', 'smart-post-show' ),
				'icon'   => 'fa fa-external-link-square',
				'fields' => array(
					array(
						'id'       => 'pcp_page_link_type',
						'class'    => 'pcp_page_link_type',
						'type'     => 'radio',
						'title'    => __( 'Detail Page Link Type', 'smart-post-show' ),
						'subtitle' => __( 'Choose a link type for the (item) detail page.', 'smart-post-show' ),
						'desc'     => __( 'To unlock the more amazing popup settings, <a href="https://smartpostshow.com/" target="_blank"><strong>Upgrade To Pro!</strong></a>', 'smart-post-show' ),
						'options'  => array(
							'popup'       => __( 'Popup (Pro)', 'smart-post-show' ),
							'single_page' => __( 'Single Page', 'smart-post-show' ),
							'none'        => __( 'None (no link action)', 'smart-post-show' ),
						),
						'default'  => 'single_page',
					),
					array(
						'id'         => 'pcp_link_target',
						'type'       => 'radio',
						'title'      => __( 'Target', 'smart-post-show' ),
						'subtitle'   => __( 'Set a target for the item link.', 'smart-post-show' ),
						'options'    => array(
							'_self'   => __( 'Current Tab', 'smart-post-show' ),
							'_blank'  => __( 'New Tab', 'smart-post-show' ),
							'_parent' => __( 'Parent', 'smart-post-show' ),
							'_top'    => __( 'Top', 'smart-post-show' ),
						),
						'default'    => '_self',
						'dependency' => array( 'pcp_page_link_type', '==', 'single_page' ),
					),
					array(
						'id'      => 'pcp_link_rel',
						'type'    => 'checkbox',
						'title'   => __( 'Add rel="nofollow" to item links', 'smart-post-show' ),
						'default' => 'false',
					),
				), // End of fields array.
			)
		); // Display settings section end.
	}
}
