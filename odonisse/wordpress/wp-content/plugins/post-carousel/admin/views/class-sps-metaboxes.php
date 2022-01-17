<?php
/**
 * The main class for Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * Smart Post Show Metaboxes.
 */
class SPS_Metaboxes {

	/**
	 * Layout Metabox function.
	 *
	 * @param string $prefix The meta-key for this metabox.
	 * @return void
	 */
	public static function layout_metabox( $prefix ) {
		SP_PC::createMetabox(
			$prefix,
			array(
				'title'        => __( 'Smart Post Show', 'smart-post-show' ),
				'post_type'    => 'sp_post_carousel',
				'show_restore' => false,
				'context'      => 'normal',
			)
		);

		SPS_Layout::section( $prefix );

	}

	/**
	 * Option Metabox function
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function option_metabox( $prefix ) {
		SP_PC::createMetabox(
			$prefix,
			array(
				'title'        => __( 'View Options', 'smart-post-show' ),
				'post_type'    => 'sp_post_carousel',
				'show_restore' => false,
				'theme'        => 'light',
			)
		);

		SPS_FilterPost::section( $prefix );
		SPS_Display::section( $prefix );
		SPS_Carousel::section( $prefix );
		SPS_DetailSettings::section( $prefix );
		SPS_Typography::section( $prefix );
	}
	/**
	 * Shortcode Metabox function
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function shortcode_metabox( $prefix ) {
		SP_PC::createMetabox(
			$prefix,
			array(
				'title'        => __( 'How To Use', 'smart-post-show' ),
				'post_type'    => 'sp_post_carousel',
				'context'      => 'side',
				'show_restore' => false,
			)
		);

		SPS_Shortcode::section( $prefix );

	}

}
