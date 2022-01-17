<?php
/**
 * The Display options Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * The Layout building class.
 */
class SPS_Display {

	/**
	 * Display options section metabox.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'DISPLAY OPTIONS', 'smart-post-show' ),
				'icon'   => 'fa fa-th-large',
				'fields' => array(
					array(
						'id'         => 'section_title',
						'type'       => 'switcher',
						'title'      => __( 'Section Title ', 'smart-post-show' ),
						'subtitle'   => __( 'Show/Hide item showcase section title.', 'smart-post-show' ),
						'default'    => false,
						'text_on'    => __( 'Show', 'smart-post-show' ),
						'text_off'   => __( 'Hide', 'smart-post-show' ),
						'text_width' => 75,
					),
					array(
						'id'              => 'section_title_margin',
						'type'            => 'spacing',
						'title'           => __( 'Section Title Margin', 'smart-post-show' ),
						'subtitle'        => __( 'Set margin for the section title.', 'smart-post-show' ),
						'all_icon'        => '<i class="fa fa-long-arrow-down"></i>',
						'units'           => array(
							'px',
						),
						'all_placeholder' => 'margin',
						'default'         => array(
							'top'    => '0',
							'right'  => '0',
							'bottom' => '30',
							'top'    => '0',
						),
						'dependency'      => array(
							'section_title',
							'==',
							'true',
							true,
						),
					),
					array(
						'id'       => 'pcp_number_of_columns',
						'type'     => 'column',
						'title'    => __( 'Column(s)', 'smart-post-show' ),
						'subtitle' => __( 'Set number of column(s) in different devices for responsive view.', 'smart-post-show' ),
						'default'  => array(
							'lg_desktop'       => '4',
							'desktop'          => '4',
							'tablet'           => '3',
							'mobile_landscape' => '2',
							'mobile'           => '1',
						),
						'min'      => '1',
						'help'     => '<i class="fa fa-television"></i> Large Desktop - is larger than 1200px,<br><i class="fa fa-desktop"></i> Desktop - size is smaller than 992px,<br> <i class="fa fa-tablet"></i> Tablet - Size is larger than 768,<br> <i class="fa fa-mobile"></i> Mobile Landscape- size is larger than 576px.,<br> <i class="fa fa-mobile"></i> Mobile - size is smaller than 576px.',
					),
					array(
						'id'              => 'margin_between_post',
						'type'            => 'spacing',
						'title'           => __( 'Margin Between Columns', 'smart-post-show' ),
						'subtitle'        => __( 'Set margin between columns (items).', 'smart-post-show' ),
						'all'             => true,
						'all_icon'        => '<i class="fa fa-arrows-h"></i>',
						'units'           => array(
							'px',
						),
						'all_placeholder' => 'margin',
						'default'         => array(
							'all' => '20',
						),
					),
					array(
						'id'       => 'post_content_orientation',
						'type'     => 'layout_preset',
						'title'    => __( 'Content Orientation ', 'smart-post-show' ),
						'subtitle' => __( 'Set a position for the item content.', 'smart-post-show' ),
						'desc'     => __( 'To unlock more amazing Content Orientation and Layout based Settings, <a href="https://smartpostshow.com/" target="_blank"><strong>Upgrade To Pro!</strong></a>', 'smart-post-show' ),
						'class'    => 'pcp-content-orientation',
						'options'  => array(
							'default'     => array(
								'image' => SP_PC_URL . 'admin/views/sp-framework/assets/img/default.png',
								'text'  => __( 'Default', 'smart-post-show' ),
							),
							'left-thumb'  => array(
								'image'    => SP_PC_URL . 'admin/views/sp-framework/assets/img/left-image.png',
								'text'     => __( 'Left Image', 'smart-post-show' ),
								'pro_only' => true,
							),
							'right-thumb' => array(
								'image'    => SP_PC_URL . 'admin/views/sp-framework/assets/img/right-image.png',
								'text'     => __( 'Right Image', 'smart-post-show' ),
								'pro_only' => true,
							),
							'overlay'     => array(
								'image'    => SP_PC_URL . 'admin/views/sp-framework/assets/img/overlay.png',
								'text'     => __( 'Overlay', 'smart-post-show' ),
								'pro_only' => true,
							),
							'card'        => array(
								'image'    => SP_PC_URL . 'admin/views/sp-framework/assets/img/card.png',
								'text'     => __( 'Card', 'smart-post-show' ),
								'pro_only' => true,
							),
							'overlay-box' => array(
								'image'    => SP_PC_URL . 'admin/views/sp-framework/assets/img/overlay-box.png',
								'text'     => __( 'Overlay Box', 'smart-post-show' ),
								'pro_only' => true,
							),
						),
						'default'  => 'default',
					),
					array(
						'id'       => 'post_border',
						'type'     => 'border',
						'title'    => __( 'Border', 'smart-post-show' ),
						'subtitle' => __( 'Set border for the item.', 'smart-post-show' ),
						'all'      => true,
						'default'  => array(
							'all'   => '0',
							'style' => 'solid',
							'color' => '#e2e2e2',
						),
					),
					array(
						'id'       => 'post_border_radius_property',
						'type'     => 'spacing',
						'title'    => __( 'Border Radius', 'smart-post-show' ),
						'subtitle' => __( 'Set border radius for item.', 'smart-post-show' ),
						'all'      => true,
						'units'    => array( 'px', '%' ),
						'default'  => array(
							'all' => '0',
						),
					),
					array(
						'id'       => 'post_background_property',
						'type'     => 'color',
						'title'    => __( 'Background', 'smart-post-show' ),
						'subtitle' => __( 'Set background color for the item.', 'smart-post-show' ),
						'default'  => 'transparent',
					),
					array(
						'id'       => 'post_inner_padding_property',
						'type'     => 'spacing',
						'title'    => __( 'Inner Padding', 'smart-post-show' ),
						'subtitle' => __( 'Set inner padding for  item.', 'smart-post-show' ),
						'default'  => array(
							'top'    => '0',
							'right'  => '0',
							'bottom' => '0',
							'left'   => '0',
							'unit'   => 'px',
						),
						'help'     => "<img src='" . SP_PC_URL . "/admin/assets/img/inner_padding.jpg'>",
					),
					array(
						'id'       => 'post_content_sorter',
						'type'     => 'sortable',
						'title'    => __( 'Content Fields', 'smart-post-show' ),
						'subtitle' => __( 'Item content fields which are draggable to change display order and it\'s settings.', 'smart-post-show' ),
						'desc'     => __( 'To show Social Share and Custom Fields, <a href="https://smartpostshow.com/" target="_blank"><strong>Upgrade To Pro!</strong></a>', 'smart-post-show' ),
						'class'    => 'post_content_sorter',
						'fields'   => array(
							array(
								'id'         => 'pcp_post_thumb',
								'type'       => 'accordion',
								'accordions' => array(
									array(
										'title'  => __( 'Thumbnail', 'smart-post-show' ),
										'fields' => array(
											array(
												'id'       => 'post_thumb_show',
												'type'     => 'switcher',
												'title'    => __( 'Thumbnail', 'smart-post-show' ),
												'text_on'  => __( 'Show', 'smart-post-show' ),
												'text_off' => __( 'Hide', 'smart-post-show' ),
												'default'  => true,
												'text_width' => 80,
											),
											array(
												'id'      => 'pcp_thumb_sizes',
												'type'    => 'image_sizes',
												'title'   => __( 'Size', 'smart-post-show' ),
												'default' => 'full',
												'dependency' => array( 'post_thumb_show', '==', 'true', true ),
											),
										),
									),
								),
							),
							array(
								'id'         => 'pcp_post_title',
								'type'       => 'accordion',
								'accordions' => array(
									array(
										'title'  => 'Title',
										'fields' => array(
											array(
												'id'       => 'show_post_title',
												'type'     => 'switcher',
												'title'    => __( 'Title', 'smart-post-show' ),
												'text_on'  => __( 'Show', 'smart-post-show' ),
												'text_off' => __( 'Hide', 'smart-post-show' ),
												'default'  => true,
												'text_width' => 80,
											),
											array(
												'id'      => 'post_title_tag',
												'type'    => 'select',
												'title'   => __( 'Title HTML Tag', 'smart-post-show' ),
												'options' => array(
													'h1'  => __( 'h1', 'smart-post-show' ),
													'h2'  => __( 'h2', 'smart-post-show' ),
													'h3'  => __( 'h3', 'smart-post-show' ),
													'h4'  => __( 'h4', 'smart-post-show' ),
													'h5'  => __( 'h5', 'smart-post-show' ),
													'h6'  => __( 'h6', 'smart-post-show' ),
													'p'   => __( 'p', 'smart-post-show' ),
													'div' => __( 'div', 'smart-post-show' ),
												),
												'default' => 'h2',
												'class'   => 'chosen',
												'dependency' => array( 'show_post_title', '==', 'true', true ),
											),
										),
									),
								),
							),
							array(
								'id'         => 'pcp_post_meta',
								'type'       => 'accordion',
								'accordions' => array(
									array(
										'title'  => __( 'Meta Fields', 'smart-post-show' ),
										'fields' => array(
											array(
												'id'       => 'show_post_meta',
												'type'     => 'switcher',
												'title'    => __( 'Meta Fields', 'smart-post-show' ),
												'text_on'  => __( 'Show', 'smart-post-show' ),
												'text_off' => __( 'Hide', 'smart-post-show' ),
												'default'  => true,
												'text_width' => 80,
											),
											array(
												'id'      => 'pcp_post_meta_group',
												'class'   => 'pcp_custom_group_design',
												'type'    => 'group',
												'button_title' => __( 'Add New Meta', 'smart-post-show' ),
												'dependency' => array( 'show_post_meta', '==', 'true' ),
												'fields'  => array(
													array(
														'id'       => 'select_post_meta',
														'type'     => 'select',
														'title'    => __( 'Select Meta', 'smart-post-show' ),
														'placeholder' => __( 'Select a meta', 'smart-post-show' ),
														'class' => 'pcp-meta-select',
														'options'  => array(
															'author'  => __( 'Author', 'smart-post-show' ),
															'date'   => __( 'Date', 'smart-post-show' ),
															'comment_count'  => __( 'Comment Count', 'smart-post-show' ),
															'taxonomy'   => __( 'Taxonomy (Pro)', 'smart-post-show' ),
															'view_count' => __( 'View Count (Pro)', 'smart-post-show' ),
															'like'     => __( 'Like (Pro)', 'smart-post-show' ),
															'reading_time'     => __( 'Reading Time (Pro)', 'smart-post-show' ),
														),
													),
													array(
														'id'      => 'select_meta_icon',
														'type'    => 'icon',
														'title'   => __( 'Meta Icon', 'smart-post-show' ),
														'default' => 'fa fa-folder-o',
														'dependency' => array( 'select_post_meta', '!=', '', true ),

													),

												),
												'default' => array(
													array(
														'select_post_meta'     => 'author',
														'select_meta_icon'    => 'fa fa-user',
													),
													array(
														'select_post_meta'     => 'date',
														'select_meta_icon'    => 'fa fa-calendar',
													),
												),
											),
										),
									),
								),
							),
							array(
								'id'         => 'pcp_post_content',
								'type'       => 'accordion',
								'accordions' => array(
									array(
										'title'  => 'Content',
										'fields' => array(
											array(
												'id'       => 'show_post_content',
												'type'     => 'switcher',
												'title'    => __( 'Content', 'smart-post-show' ),
												'text_on'  => __( 'Show', 'smart-post-show' ),
												'text_off' => __( 'Hide', 'smart-post-show' ),
												'default'  => true,
												'text_width' => 80,
											),
											array(
												'id'      => 'post_content_type',
												'type'    => 'select',
												'class'   => 'pcp-post-content-type',
												'title'   => __( 'Content Display Type', 'smart-post-show' ),
												'options' => array(
													'excerpt'      => __( 'Excerpt', 'smart-post-show' ),
													'full_content' => __( 'Full Content', 'smart-post-show' ),
													'limit_content' => __( 'Content with Limit (Pro)', 'smart-post-show' ),
												),
												'default' => 'excerpt',
												'dependency' => array( 'show_post_content', '==', 'true', true ),
											),

											// ReadMore settings.
											array(
												'id'       => 'show_read_more',
												'type'     => 'switcher',
												'title'    => __( 'Read More', 'smart-post-show' ),
												'text_on'  => __( 'Show', 'smart-post-show' ),
												'text_off' => __( 'Hide', 'smart-post-show' ),
												'default'  => true,
												'text_width' => 80,
												'dependency' => array( 'show_post_content|post_content_type', '==|!=', 'true|full_content', true ),
											),
											array(
												'id'      => 'pcp_read_label',
												'type'    => 'text',
												'title'   => __( 'Read More Label', 'smart-post-show' ),
												'default' => 'Read More',
												'dependency' => array( 'show_post_content|post_content_type|show_read_more', '==|!=|==', 'true|full_content|true', true ),
											),
											array(
												'id'      => 'readmore_color_button',
												'type'    => 'color_group',
												'title'   => __( 'Read More Color', 'smart-post-show' ),
												'options' => array(
													'standard' => __( 'Text Color', 'smart-post-show' ),
													'hover' => __( 'Text Hover Color', 'smart-post-show' ),
													'bg' => __( 'Background', 'smart-post-show' ),
													'hover_bg' => __( 'Hover Background', 'smart-post-show' ),
													'border' => __( 'Border', 'smart-post-show' ),
													'hover_border' => __( 'Hover Border', 'smart-post-show' ),
												),
												'default' => array(
													'standard' => '#111',
													'hover' => '#fff',
													'bg' => 'transparent',
													'hover_bg' => '#e1624b',
													'border' => '#888',
													'hover_border' => '#e1624b',
												),
												'dependency' => array( 'show_post_content|post_content_type|show_read_more', '==|!=|==', 'true|full_content|true', true ),
											),

										),
									),
								),
							),
							array(
								'id'         => 'pcp_social_share',
								'type'       => 'accordion',
								'class'      => 'pcp-pro-only',
								'accordions' => array(
									array(
										'title'  => __( 'Social Share (Pro)', 'smart-post-show' ),
										'fields' => array(
											array(
												'id'      => 'social_position',
												'type'    => 'button_set',
												'title'   => __( 'Alignment', 'smart-post-show' ),
												'options' => array(
													'left' => '<i class="fa fa-align-left" title="Left"></i>',
													'center' => '<i class="fa fa-align-center" title="Center"></i>',
													'right' => '<i class="fa fa-align-right" title="Right"></i>',
												),
												'default' => 'left',
												'dependency' => array( 'social_sharing_media', '!=', '' ),
											),
										),
									),
								),
							), // PCP Post Social.
							array(
								'id'         => 'pcp_custom_fields',
								'type'       => 'accordion',
								'class'      => 'pcp-pro-only',
								'accordions' => array(
									array(
										'title'  => __( 'Custom Fields (Pro)', 'smart-post-show' ),
										'fields' => array(
											// The Group Fields.
											array(
												'id'   => 'pcp_custom_meta_icon',
												'type' => 'icon',
												'title' => 'Icon Before Name',
												'default' => 'fa fa-calendar-o',
											),
													
										), // End Fields array.
									),
								), // Accordion.
							), // Custom fields end.
						),
					),
					array(
						'id'         => 'show_post_pagination',
						'type'       => 'switcher',
						'text_on'    => __( 'Enabled', 'smart-post-show' ),
						'text_off'   => __( 'Disabled', 'smart-post-show' ),
						'text_width' => 100,
						'title'      => __( 'Pagination', 'smart-post-show' ),
						'subtitle'   => __( 'Enabled/Disabled item pagination.', 'smart-post-show' ),
						'default'    => true,
						'dependency' => array( 'pcp_layout_preset', 'not-any', 'carousel_layout', 'true' ),
					),
					array(
						'id'         => 'post_pagination_type',
						'type'       => 'radio',
						'title'      => __( 'Pagination Type', 'smart-post-show' ),
						'subtitle'   => __( 'Choose a pagination type.', 'smart-post-show' ),
						'desc'       => __( 'More amazing Ajax Pagination Settings are available in <a href="https://smartpostshow.com/" target="_blank"><strong>Pro</strong></a>!', 'smart-post-show' ),
						'class'      => 'pcp-pagination-type',
						'options'    => array(
							'no_ajax'         => __( 'Normal Pagination', 'smart-post-show' ),
							'ajax_load_more'  => __( 'Ajax Load More Button (Pro)', 'smart-post-show' ),
							'ajax_pagination' => __( 'Ajax Number Pagination (Pro)', 'smart-post-show' ),
							'infinite_scroll' => __( 'Ajax Infinite Scroll (Pro)', 'smart-post-show' ),
						),
						'default'    => 'no_ajax',
						'dependency' => array( 'pcp_layout_preset|show_post_pagination', 'not-any|==', 'carousel_layout|true', true ),
						// 'dependency' => array( 'pcp_layout_preset', 'not-any', 'grid_layout', 'true' ),
					),
					array(
						'id'         => 'pcp_pagination_btn_color',
						'type'       => 'color_group',
						'title'      => __( 'Pagination  Color', 'smart-post-show' ),
						'subtitle'   => __( 'Set Pagination color', 'smart-post-show' ),
						'options'    => array(
							'text_color'        => __( 'Text Color', 'smart-post-show' ),
							'text_acolor'       => __( 'Text Active Color', 'smart-post-show' ),
							'border_color'      => __( 'Border Color', 'smart-post-show' ),
							'border_acolor'     => __( 'Border Active Color', 'smart-post-show' ),
							'background'        => __( 'Background', 'smart-post-show' ),
							'active_background' => __( 'Active BG', 'smart-post-show' ),
						),
						'default'    => array(
							'text_color'        => '#5e5e5e',
							'text_acolor'       => '#ffffff',
							'border_color'      => '#bbbbbb',
							'border_acolor'     => '#e1624b',
							'background'        => '#ffffff',
							'active_background' => '#e1624b',
						),
						'dependency' => array( 'pcp_layout_preset|show_post_pagination|post_pagination_type', '!=|==|any', 'carousel_layout|true|ajax_pagination,no_ajax', true ),
					),
					array(
						'id'         => 'post_per_page',
						'type'       => 'spinner',
						'title'      => __( 'Items Per Page', 'smart-post-show' ),
						'subtitle'   => __( 'Set number of items to show per page.', 'smart-post-show' ),
						'help'   	 => __( 'This value should be lesser than that <strong> Limit </strong> from <strong>Filter Content  </strong> tab.', 'smart-post-show' ),
						'default'    => 12,
						'dependency' => array( 'pcp_layout_preset|show_post_pagination', '==|==', 'grid_layout|true' ),
					),

					array(
						'id'       => 'show_preloader',
						'type'     => 'switcher',
						'title'    => __( 'Preloader', 'smart-post-show' ),
						'subtitle' => __( 'Items will be hidden until page load completed.', 'smart-post-show' ),
						'default'  => false,
					),
				), // End of fields array.
			)
		); // Display settings section end.
	}
}
