<?php
/**
 * File for the public facing functions.
 *
 * @link        https://smartpostshow.com/
 * @since      2.2.0
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/public
 * @author     ShapedPlugin <support@shapedplugin.com>
 */
class Smart_Post_Show_Public {

	/**
	 * Script and style suffix
	 *
	 * @since 2.2.0
	 * @access protected
	 * @var string
	 */
	protected $suffix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.2.0
	 */
	public function __construct() {
		$this->load_public_dependencies();
		$this->pcp_public_action();
	}

	/**
	 * Public dependencies.
	 *
	 * @return void
	 */
	private function load_public_dependencies() {
		require_once SP_PC_PATH . 'public/helpers/class-post-functions.php';
		require_once SP_PC_PATH . 'public/helpers/class-pcp-queryinside.php';
		require_once SP_PC_PATH . 'public/template/loop/class-loop-html.php';
	}

	/**
	 * Public Action
	 *
	 * @return void
	 */
	private function pcp_public_action() {
		add_shortcode( 'smart_post_show', array( $this, 'pcp_shortcode_render' ) );
		add_shortcode( 'sp_postcarousel', array( $this, 'pcp_shortcode_render' ) );
		add_shortcode( 'post-carousel', array( $this, 'pcp_shortcode_render' ) );

		$this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 2.2.0
	 */
	public function enqueue_styles() {

		$pcp_settings = get_option( 'sp_post_carousel_settings' );
		if ( $pcp_settings['pcp_fontawesome_css'] ) {
			wp_enqueue_style( 'font-awesome', SP_PC_URL . 'public/assets/css/font-awesome.min.css', array(), SP_PC_VERSION, 'all' );
		}
		if ( $pcp_settings['pcp_swiper_css'] ) {
			wp_enqueue_style( 'pcp_swiper', SP_PC_URL . 'public/assets/css/swiper-bundle' . $this->suffix . '.css', array(), SP_PC_VERSION, 'all' );
		}
		wp_enqueue_style( 'pcp-style', SP_PC_URL . 'public/assets/css/style' . $this->suffix . '.css', array(), SP_PC_VERSION, 'all' );

		$pcp_posts      = new WP_Query(
			array(
				'post_type'      => 'sp_post_carousel',
				'posts_per_page' => 900,
			)
		);
		$post_ids       = wp_list_pluck( $pcp_posts->posts, 'ID' );
		$custom_css     = '';
		$pcp_custom_css = $pcp_settings['pcp_custom_css'];
		foreach ( $post_ids as $pcp_id ) {
			// Include dynamic style file.
			include 'dynamic-css/dynamic-css.php';
		}
		if ( ! empty( $pcp_custom_css ) ) {
			$custom_css .= $pcp_custom_css;
		}
		// Add dynamic style.
		wp_add_inline_style( 'pcp-style', $custom_css );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.2.0
	 */
	public function enqueue_scripts() {
		wp_register_script( 'pcp_swiper', SP_PC_URL . 'public/assets/js/swiper-bundle' . $this->suffix . '.js', array( 'jquery' ), SP_PC_VERSION, true );
		wp_register_script( 'pcp_script', SP_PC_URL . 'public/assets/js/scripts' . $this->suffix . '.js', array( 'jquery' ), SP_PC_VERSION, true );
	}

	/**
	 * PCP shortcode markup wrapper classes.
	 *
	 * @param string $layout_preset The selected layout name.
	 * @param int    $shortcode_id The shortcode ID.
	 */
	public static function pcp_wrapper_classes( $layout_preset, $shortcode_id ) {
		$wrapper_class = "sp-pcp-section sp-pcp-container pcp-wrapper-{$shortcode_id}";
		switch ( $layout_preset ) {
			case 'carousel_layout':
				$wrapper_class .= ' pcp-carousel-wrapper';
				break;
		}
		echo esc_attr( $wrapper_class );
	}

	/**
	 * Shortcode Wrapper data attributes.
	 *
	 * @param int $shortcode_id The shortcode ID.
	 * @return void
	 */
	public static function wrapper_data( $shortcode_id ) {
		$wrapper_data = " data-pagination= 'no_ajax'";

		if ( $shortcode_id ) {
			$wrapper_data .= " data-sid={$shortcode_id}";
		}
		echo esc_html( $wrapper_data );
	}

	/**
	 * HTML classes for Post.
	 *
	 * @return string
	 */
	public static function pcp_alt_post_class() {
			$pcp_alt_post_class = 'sp-pcp-post';
		return apply_filters( 'post_post_class_name', $pcp_alt_post_class );
	}

	/**
	 * Post responsive columns class.
	 *
	 * @param string $layout Layout preset.
	 * @param string $columns Columns number.
	 * @return string
	 */
	public static function pcp_post_responsive_columns( $layout, $columns ) {
		$pcp_post_columns = '';
		if ( 'carousel_layout' === $layout ) {
			$pcp_post_columns .= ' swiper-slide swiper-lazy';
		} else {
			$pcp_post_columns .= " sp-pcp-col-xs-$columns[mobile] sp-pcp-col-sm-$columns[mobile_landscape] sp-pcp-col-md-$columns[tablet] sp-pcp-col-lg-$columns[desktop] sp-pcp-col-xl-$columns[lg_desktop]";
		}
		return $pcp_post_columns;
	}

	/**
	 * Post Loop.
	 *
	 * @param array  $options Views options.
	 * @param string $layout Layout preset.
	 * @param array  $sorter Post sorting options.
	 * @param int    $scode_id Shortcode ID.
	 * @return void
	 */
	public static function pcp_post_loop( $options, $layout, $sorter, $scode_id ) {
		global $post;
		$number_of_columns = SP_PC_Functions::pcp_metabox_value( 'pcp_number_of_columns', $options );
		?>
		<div class="<?php echo esc_attr( self::pcp_post_responsive_columns( $layout, $number_of_columns ) ); ?>">
			<div class="sp-pcp-post pcp-item-<?php echo esc_attr( $post->ID ); ?>" data-id="<?php echo esc_attr( $post->ID ); ?>">
				<?php
					SP_PC_HTML::pcp_post_content_with_thumb( $sorter, $layout, $scode_id, $post, $options );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get all query posts.
	 *
	 * @param array  $options Views options.
	 * @param array  $layout Layout preset.
	 * @param array  $sorter  Post sorting options.
	 * @param object $pcp_query post query.
	 * @param int    $view_id Shortcode ID.
	 * @return void
	 */
	public static function pcp_get_posts( $options, $layout, $sorter, $pcp_query, $view_id ) {
		$pcp_count = 1;
		while ( $pcp_query->have_posts() ) {
			$pcp_query->the_post();
			self::pcp_post_loop( $options, $layout, $sorter, $view_id );
			$pcp_count++;
		}
		wp_reset_postdata();
	}

	/**
	 * Function get layout from atts and create class depending on it.
	 *
	 * @since 2.0
	 * @param array $attribute attribute of this shortcode.
	 */
	public function pcp_shortcode_render( $attribute ) {
		if ( empty( $attribute['id'] ) ) {
			return;
		}
		$pcp_gl_id = $attribute['id']; // Smart Post Show global ID for Shortcode metaboxes.
		// Setting options.
		$pcp_settings = get_option( 'sp_post_carousel_settings' );
		// Preset Layouts.
		$layout        = get_post_meta( $pcp_gl_id, 'sp_pcp_layouts', true );
		$layout_preset = isset( $layout['pcp_layout_preset'] ) ? $layout['pcp_layout_preset'] : '';
		// All the visible options for the Shortcode like – Global, Filter, Display, Popup, Typography etc.
		$options              = SP_PC_Functions::view_options( $pcp_gl_id );
		$view_options         = get_post_meta( $pcp_gl_id, 'sp_pcp_view_options', true );
		$post_content_sorter  = isset( $view_options['post_content_sorter'] ) ? $view_options['post_content_sorter'] : '';
		$pcp_content_position = isset( $view_options['post_content_orientation'] ) ? $view_options['post_content_orientation'] : '';
		$margin_between_post  = isset( $view_options['margin_between_post']['all'] ) ? $view_options['margin_between_post']['all'] : '';
		$show_preloader       = isset( $view_options['show_preloader'] ) ? $view_options['show_preloader'] : 0;
		$query_args           = SP_PC_QueryInside::get_filtered_content( $pcp_gl_id );
		$pcp_query            = new WP_Query( $query_args );
		$total_post_count     = $pcp_query->post_count;
		// Pagination.
		$show_pagination = isset( $view_options['show_post_pagination'] ) ? $view_options['show_post_pagination'] : '';

		wp_enqueue_script( 'pcp_script' );
		$pcp_custom_js = $pcp_settings['pcp_custom_js'];
		if ( ! empty( $pcp_custom_js ) ) {
			wp_add_inline_script( 'pcp_script', $pcp_custom_js );
		}
		ob_start();
		if ( 'carousel_layout' === $layout_preset ) {
			require SP_PC_TEMPLATE_PATH . '/carousel.php';
		} elseif ( 'grid_layout' === $layout_preset ) {
			require SP_PC_TEMPLATE_PATH . '/grid.php';
		}
		return ob_get_clean();
	}
}
