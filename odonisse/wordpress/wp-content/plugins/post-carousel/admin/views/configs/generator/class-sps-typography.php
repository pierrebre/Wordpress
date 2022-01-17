<?php
/**
 * The Typography Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * The Typography class.
 */
class SPS_Typography {

	/**
	 * Typography section metabox.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'           => __( 'Typography', 'smart-post-show' ),
				'icon'            => 'fa fa-font',
				'enqueue_webfont' => true,
				'fields'          => array(
					array(
						'content' => __( 'To unlock the following typography (950+ Google Fonts) options, <a href="https://smartpostshow.com/" target="_blank"><strong>Upgrade To Pro!</strong></a> P.S. Note: The color fields work in the lite version.', 'smart-post-show' ),
						'type'    => 'notice',
					),
					array(
						'id'         => 'section_title_typography',
						'type'       => 'typography',
						'title'      => __( 'Section Title', 'smart-post-show' ),
						'subtitle'   => __( 'Set item showcase section title font properties.', 'smart-post-show' ),
						'default'    => array(
							'color'              => '#444',
							'font-family'        => '',
							'font-weight'        => '',
							'subset'             => '',
							'font-size'          => '24',
							'tablet-font-size'   => '20',
							'mobile-font-size'   => '18',
							'line-height'        => '28',
							'tablet-line-height' => '24',
							'mobile-line-height' => '20',
							'letter-spacing'     => '0',
							'text-align'         => 'left',
							'text-transform'     => 'none',
							'type'               => '',
							'unit'               => 'px',
						),
						'dependency' => array( 'section_title', '==', 'true', 'all' ),
					),
					array(
						'id'          => 'post_title_typography',
						'type'        => 'typography',
						'title'       => __( 'Title', 'smart-post-show' ),
						'subtitle'    => __( 'Set title font properties.', 'smart-post-show' ),
						'hover_color' => true,
						'default'     => array(
							'color'              => '#111',
							'hover_color'        => '#e1624b',
							'font-family'        => '',
							'font-weight'        => '',
							'subset'             => '',
							'font-size'          => '20',
							'tablet-font-size'   => '18',
							'mobile-font-size'   => '16',
							'line-height'        => '24',
							'tablet-line-height' => '22',
							'mobile-line-height' => '20',
							'letter-spacing'     => '0',
							'text-align'         => 'left',
							'text-transform'     => 'none',
							'type'               => '',
							'unit'               => 'px',
						),
						'dependency'  => array( 'show_post_title', '==', 'true', 'all' ),
					),
					array(
						'id'          => 'post_meta_typography',
						'type'        => 'typography',
						'title'       => __( 'Meta Fields', 'smart-post-show' ),
						'subtitle'    => __( 'Set meta fields font properties.', 'smart-post-show' ),
						'hover_color' => true,
						'default'     => array(
							'color'              => '#888',
							'hover_color'        => '#e1624b',
							'font-family'        => '',
							'font-weight'        => '',
							'subset'             => '',
							'font-size'          => '14',
							'tablet-font-size'   => '14',
							'mobile-font-size'   => '12',
							'line-height'        => '16',
							'tablet-line-height' => '16',
							'mobile-line-height' => '16',
							'letter-spacing'     => '0',
							'text-align'         => 'left',
							'text-transform'     => 'none',
							'type'               => '',
							'unit'               => 'px',
						),
					),
					array(
						'id'         => 'post_content_typography',
						'type'       => 'typography',
						'title'      => __( 'Content', 'smart-post-show' ),
						'subtitle'   => __( 'Set content font properties.', 'smart-post-show' ),
						'default'    => array(
							'color'              => '#444',
							'font-family'        => '',
							'font-weight'        => '',
							'subset'             => '',
							'font-size'          => '16',
							'tablet-font-size'   => '14',
							'mobile-font-size'   => '12',
							'line-height'        => '20',
							'tablet-line-height' => '18',
							'mobile-line-height' => '18',
							'letter-spacing'     => '0',
							'text-align'         => 'left',
							'text-transform'     => 'none',
							'type'               => '',
							'unit'               => 'px',
						),
						'dependency' => array( 'show_post_content', '==', 'true', 'all' ),
					),
					array(
						'id'         => 'read_more_typography',
						'type'       => 'typography',
						'title'      => __( 'Read More', 'smart-post-show' ),
						'subtitle'   => __( 'Set read more font properties.', 'smart-post-show' ),
						'color'      => false,
						'default'    => array(
							'font-family'        => '',
							'font-weight'        => '600',
							'subset'             => '',
							'font-size'          => '12',
							'tablet-font-size'   => '12',
							'mobile-font-size'   => '10',
							'line-height'        => '18',
							'tablet-line-height' => '18',
							'mobile-line-height' => '16',
							'letter-spacing'     => '0',
							'text-align'         => 'left',
							'text-transform'     => 'uppercase',
							'type'               => '',
							'unit'               => 'px',
						),
						'dependency' => array( 'show_read_more|show_post_content', '==|==', 'true|true', 'all' ),
					),

					array(
						'id'       => 'custom_fields_typography',
						'type'     => 'typography',
						'title'    => __( 'Custom Fields', 'smart-post-show' ),
						'subtitle' => __( 'Set custom fields font properties.', 'smart-post-show' ),
						'color'    => false,
						'default'  => array(
							// 'color'              => '#888',
																	// 'hover_color'        => '#444',
																	'font-family' => '',
							'font-weight'        => '',
							'subset'             => '',
							'font-size'          => '14',
							'tablet-font-size'   => '14',
							'mobile-font-size'   => '12',
							'line-height'        => '18',
							'tablet-line-height' => '18',
							'mobile-line-height' => '16',
							'letter-spacing'     => '0',
							'text-align'         => 'left',
							'text-transform'     => 'none',
							'type'               => '',
							'unit'               => 'px',
						),
					),
				), // End of fields array.
			)
		);
	}
}
