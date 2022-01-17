<?php
/**
 * The layout Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

	/**
	 * The Layout building class.
	 */
class SPS_Layout {

	/**
	 * Layout metabox section.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'fields' => array(
					array(
						'type'  => 'heading',
						'image' => SP_PC_URL . 'admin/assets/img/logo.svg',
						'after' => '<i class="fa fa-life-ring"></i> Support',
						'link'  => 'https://shapedplugin.com/support/?user=lite',
						'class' => 'pcp-admin-header',
					),
					array(
						'id'      => 'pcp_layout_preset',
						'type'    => 'layout_preset',
						'title'   => __( 'Layout Preset', 'smart-post-show' ),
						'class'   => 'pcp-layout-preset',
						'options' => array(
							'carousel_layout'  => array(
								'image' => SP_PC_URL . 'admin/assets/img/carousel.png',
								'text'  => __( 'Carousel', 'smart-post-show' ),
							),
							'grid_layout'      => array(
								'image' => SP_PC_URL . 'admin/assets/img/grid.png',
								'text'  => __( 'Grid', 'smart-post-show' ),
							),
							'list_layout'      => array(
								'image'    => SP_PC_URL . 'admin/assets/img/list.png',
								'text'     => __( 'List', 'smart-post-show' ),
								'pro_only' => true,
							),
							'filter_layout'    => array(
								'image'    => SP_PC_URL . 'admin/assets/img/filter.png',
								'text'     => __( 'Isotope', 'smart-post-show' ),
								'pro_only' => true,
							),
							'timeline_layout'  => array(
								'image'    => SP_PC_URL . 'admin/assets/img/timeline.png',
								'text'     => __( 'Timeline', 'smart-post-show' ),
								'pro_only' => true,
							),
							'zigzag_layout'    => array(
								'image'    => SP_PC_URL . 'admin/assets/img/zigzag.png',
								'text'     => __( 'Zigzag', 'smart-post-show' ),
								'pro_only' => true,
							),
							'accordion_layout' => array(
								'image'    => SP_PC_URL . 'admin/assets/img/collapsible.png',
								'text'     => __( 'Accordion', 'smart-post-show' ),
								'pro_only' => true,
							),
							'large_with_small' => array(
								'image'    => SP_PC_URL . 'admin/assets/img/block.png',
								'text'     => __( 'Large with Small', 'smart-post-show' ),
								'pro_only' => true,
							),
						),
						'default' => 'carousel_layout',
					),
				), // End of fields array.
			)
		);

	}
}
