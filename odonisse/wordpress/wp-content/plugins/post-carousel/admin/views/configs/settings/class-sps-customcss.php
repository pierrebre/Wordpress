<?php
/**
 * The Custom CSS and JavaScript setting configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

class SPS_CustomCSS {

	/**
	 * Custom CSS & JS settings.
	 *
	 * @param string $prefix The settings.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'Custom CSS & JS', 'smart-post-show' ),
				'icon'   => 'fa fa-css3',
				'fields' => array(
					array(
						'id'       => 'pcp_custom_css',
						'type'     => 'code_editor',
						'title'    => __( 'Custom CSS', 'smart-post-show' ),
						'settings' => array(
							'icon'  => 'fa fa-sliders',
							'theme' => 'mbo',
							'mode'  => 'css',
						),
					),
					array(
						'id'       => 'pcp_custom_js',
						'type'     => 'code_editor',
						'title'    => __( 'Custom JS', 'smart-post-show' ),
						'settings' => array(
							'icon'  => 'fa fa-sliders',
							'theme' => 'monokai',
							'mode'  => 'javascript',
						),
					),
				),
			)
		);
	}
}
