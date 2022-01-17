<?php
/**
 * The Filter Post Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * The Filter post building class.
 */
class SPS_FilterPost {

	/**
	 * Filter Post section metabox.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'Filter content', 'smart-post-show' ),
				'icon'   => 'fa fa-filter',
				'fields' => array(
					array(
						'id'          => 'pcp_select_post_type',
						'type'        => 'select',
						'title'       => __( 'Post Type(s)', 'smart-post-show' ),
						'subtitle'    => __( 'Select post type(s).', 'smart-post-show' ),
						'desc'        => __( 'To filter custom post type (product, portfolio, event...), <a href="https://smartpostshow.com/" target="_blank"><strong>Upgrade To Pro!</strong></a>', 'smart-post-show' ),
						'options'     => array(
							'post' => __( 'Posts', 'smart-post-show' ),
							'page' => __( 'Pages', 'smart-post-show' ),
						),
						'class'       => 'sp_pcp_post_type',
						'default'     => 'post',
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => __( 'Select Post Type', 'smart-post-show' ),
						'attributes'  => array(
							'style' => 'min-width: 150px;',
						),
					),
					array(
						'type'    => 'subheading',
						'content' => __( 'Common Filtering', 'smart-post-show' ),
					),
					array(
						'id'          => 'pcp_include_only_posts',
						'type'        => 'select',
						'title'       => __( 'Include Only', 'smart-post-show' ),
						'subtitle'    => __( 'Enter post IDs, or type to search by title.', 'smart-post-show' ),
						'options'     => 'posts',
						'ajax'        => true,
						'sortable'    => true,
						'chosen'      => true,
						'class'       => 'sp_pcp_include_only_posts',
						'multiple'    => true,
						'placeholder' => __( 'Choose posts', 'smart-post-show' ),
						'query_args'  => array(
							'cache_results' => false,
							'no_found_rows' => true,
						),
					),
					array(
						'id'       => 'pcp_exclude_post_set',
						'type'     => 'fieldset',
						'title'    => __( 'Exclude', 'smart-post-show' ),
						'subtitle' => __( 'Enter post IDs, or type to search by title.', 'smart-post-show' ),
						'class'    => 'sp_pcp_exclude_post_set',
						'fields'   => array(
							array(
								'id'          => 'pcp_exclude_posts',
								'type'        => 'select',
								'options'     => 'posts',
								'chosen'      => true,
								'class'       => 'sp_pcp_exclude_posts',
								'multiple'    => true,
								'ajax'        => true,
								'placeholder' => __( 'Choose posts to exclude', 'smart-post-show' ),
								'query_args'  => array(
									'cache_results' => false,
									'no_found_rows' => true,
								),
								'dependency'  => array( 'pcp_include_only_posts', '==', '', true ),
							),
							array(
								'id'      => 'pcp_exclude_too',
								'type'    => 'checkbox',
								'class'   => 'sp_pcp_exclude_too',
								'options' => array(
									'current'            => __( 'Current Post', 'smart-post-show' ),
									'password_protected' => __( 'Password Protected Posts', 'smart-post-show' ),
									'children'           => __( 'Children Posts', 'smart-post-show' ),
								),
							),
						),
					),
					array(
						'id'       => 'pcp_post_limit',
						'title'    => __( 'Limit', 'smart-post-show' ),
						'type'     => 'spinner',
						'subtitle' => __( 'Number of total items to display.', 'smart-post-show' ),
						'default'  => '20',
						'min'      => 1,
					),
					array(
						'type'    => 'subheading',
						'content' => __( 'Advanced Filtering', 'smart-post-show' ),
					),
					array(
						'id'       => 'pcp_advanced_filter',
						'type'     => 'checkbox',
						'class'    => 'spf_column_2 pcp_advanced_filter',
						'title'    => __( 'Filter by', 'smart-post-show' ),
						'subtitle' => __( 'Check the option(s) to filter by.', 'smart-post-show' ),
						'options'  => array(
							'taxonomy'     => __( 'Taxonomy', 'smart-post-show' ),
							'author'       => __( 'Author', 'smart-post-show' ),
							'sortby'       => __( 'Sort By', 'smart-post-show' ),
							'custom_field' => __( 'Custom Fields (Pro)', 'smart-post-show' ),
							'status'       => __( 'Status', 'smart-post-show' ),
							'date'         => __( 'Date (Pro)', 'smart-post-show' ),
							'keyword'      => __( 'Keyword', 'smart-post-show' ),
						),
					),
					array(
						'id'         => 'pcp_filter_by_taxonomy',
						'type'       => 'accordion',
						'class'      => 'padding-t-0 pcp-opened-accordion',
						'accordions' => array(
							array(
								'title'  => __( 'Taxonomy', 'smart-post-show' ),
								'icon'   => 'fa fa-folder-open',
								'fields' => array(
									// The Group Fields.
									array(
										'id'     => 'pcp_taxonomy_and_terms',
										'type'   => 'group',
										'class'  => 'pcp_taxonomy_terms_group pcp_custom_group_design',
										'accordion_title_auto' => true,
										'fields' => array(
											array(
												'id'      => 'pcp_select_taxonomy',
												'type'    => 'select',
												'title'   => __( 'Select Taxonomy', 'smart-post-show' ),
												'class'   => 'sp_pcp_post_taxonomy',
												'options' => 'taxonomy',
												'query_args' => array(
													'type' => 'post',
												),
												'attributes' => array(
													'style' => 'width: 200px;',
												),
												'empty_message' => __( 'No taxonomies found.', 'smart-post-show' ),
											),
											array(
												'id'       => 'pcp_select_terms',
												'type'     => 'select',
												'title'    => __( 'Choose Term(s)', 'smart-post-show' ),
												'help'     => __( 'Choose the taxonomy term(s) to show the posts from.', 'smart-post-show' ),
												'options'  => 'terms',
												'class'    => 'sp_pcp_taxonomy_terms',
												'width'    => '300px',
												'multiple' => true,
												'sortable' => true,
												'empty_message' => __( 'No terms found.', 'smart-post-show' ),
												'placeholder' => __( 'Select Term(s)', 'smart-post-show' ),
												'chosen'   => true,
											),
											array(
												'id'      => 'pcp_taxonomy_term_operator',
												'type'    => 'select',
												'title'   => __( 'Operator', 'smart-post-show' ),
												'options' => array(
													'IN'  => __( 'IN', 'smart-post-show' ),
													'AND' => __( 'AND', 'smart-post-show' ),
													'NOT IN' => __( 'NOT IN', 'smart-post-show' ),
												),
												'default' => 'IN',
												'help'    => __( 'IN - Show posts which associate with one or more terms<br>AND - Show posts which match all terms<br>NOT IN - Show posts which don\'t match the terms', 'smart-post-show' ),
											),
											array(
												'id'    => 'add_filter_post',
												'class' => 'pcp_disabled',
												'type'  => 'checkbox',
												'title' => __( 'Add to Live Filter(Pro)', 'smart-post-show' ),
											),

										),
									), // Group field end.
									array(
										'id'      => 'pcp_taxonomies_relation',
										'type'    => 'select',
										'title'   => __( 'Relation', 'smart-post-show' ),
										'class'   => 'pcp_relate_among_taxonomies',
										'options' => array(
											'AND' => __( 'AND', 'smart-post-show' ),
											'OR'  => __( 'OR', 'smart-post-show' ),
										),
										'default' => 'AND',
										'help'    => __( 'The logical relationship between/among above taxonomies.', 'smart-post-show' ),
									),

								), // Fields array.
							),
						), // Accordions end.
						'dependency' => array( 'pcp_advanced_filter', 'not-any', 'author,sortby,custom_field,status,date,keyword' ),
					),
					array(
						'id'         => 'pcp_filter_by_author',
						'type'       => 'accordion',
						'class'      => 'padding-t-0 pcp-opened-accordion',
						'accordions' => array(
							array(
								'title'  => 'Author',
								'icon'   => 'fa fa-user',
								'fields' => array(
									array(
										'id'      => 'pcp_select_author_by',
										'type'    => 'checkbox',
										'title'   => __( 'Post by Author', 'smart-post-show' ),
										'options' => 'users',
									),
									array(
										'id'      => 'pcp_select_author_not_by',
										'type'    => 'checkbox',
										'title'   => __( 'Post Not by Author ', 'smart-post-show' ),
										'options' => 'users',
									),
								),
							),
						),
						'dependency' => array( 'pcp_advanced_filter', 'not-any', 'taxonomy,sortby,custom_field,status,date,keyword' ),
					),
					array(
						'id'         => 'pcp_filter_by_order',
						'type'       => 'accordion',
						'class'      => 'padding-t-0 pcp-opened-accordion',
						'accordions' => array(
							array(
								'title'  => 'Sort By',
								'icon'   => 'fa fa-sort',
								'fields' => array(
									array(
										'id'      => 'pcp_select_filter_orderby',
										'type'    => 'select',
										'title'   => __( 'Order by', 'smart-post-show' ),
										'options' => array(
											'ID'         => __( 'ID', 'smart-post-show' ),
											'title'      => __( 'Title', 'smart-post-show' ),
											'date'       => __( 'Date', 'smart-post-show' ),
											'modified'   => __( 'Modified date', 'smart-post-show' ),
											'post__in'   => __( 'Post in (Drag & Drop) (Pro)', 'smart-post-show' ),
											'post_slug'  => __( 'Post slug (Pro)', 'smart-post-show' ),
											'post_type'  => __( 'Post type (Pro)', 'smart-post-show' ),
											'rand'       => __( 'Random (Pro)', 'smart-post-show' ),
											'comment_count' => __( 'Comment count (Pro)', 'smart-post-show' ),
											'menu_order' => __( 'Menu order (Pro)', 'smart-post-show' ),
											'author'     => __( 'Author (Pro)', 'smart-post-show' ),
										),
										'default' => 'date',
									),
									array(
										'id'         => 'pcp_select_filter_order',
										'type'       => 'radio',
										'title'      => __( 'Order', 'smart-post-show' ),
										'options'    => array(
											'ASC'  => __( 'Ascending', 'smart-post-show' ),
											'DESC' => __( 'Descending', 'smart-post-show' ),
										),
										'default'    => 'DESC',
										'dependency' => array( 'pcp_select_filter_orderby', '!=', 'post__in' ),
									),
								),
							),
						),
						'dependency' => array( 'pcp_advanced_filter', 'not-any', 'taxonomy,author,custom_field,status,date,keyword' ),
					),
					array(
						'id'         => 'pcp_filter_by_status',
						'type'       => 'accordion',
						'class'      => 'padding-t-0 pcp-opened-accordion',
						'accordions' => array(
							array(
								'title'  => __( 'Status', 'smart-post-show' ),
								'icon'   => 'fa fa-lock',
								'fields' => array(
									array(
										'id'       => 'pcp_select_post_status',
										'type'     => 'select',
										'title'    => __( 'Post Status', 'smart-post-show' ),
										'options'  => 'post_statuses',
										'multiple' => true,
										'chosen'   => true,
									),
								),
							),
						),
						'dependency' => array( 'pcp_advanced_filter', 'not-any', 'taxonomy,author,custom_field,sortby,date,keyword' ),
					),
					array(
						'id'         => 'pcp_filter_by_keyword',
						'type'       => 'accordion',
						'class'      => 'padding-t-0 pcp-opened-accordion',
						'accordions' => array(
							array(
								'title'  => __( 'Keyword', 'smart-post-show' ),
								'icon'   => 'fa fa-key',
								'fields' => array(
									array(
										'id'      => 'pcp_set_post_keyword',
										'type'    => 'text',
										'title'   => __( 'Type Keyword', 'smart-post-show' ),
										'help'    => __( 'Enter keyword(s) for searching the posts.', 'smart-post-show' ),
										'options' => 'post_statuses',
									),
								),
							),
						),
						'dependency' => array( 'pcp_advanced_filter', 'not-any', 'taxonomy,author,custom_field,sortby,date,status' ),
					),
				),
			)
		); // Filter settings section end.
	}
}
