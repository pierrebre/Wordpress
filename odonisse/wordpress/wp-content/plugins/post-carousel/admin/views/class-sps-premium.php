<?php
/**
 * The main class for Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin/assets/assets/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.

/**
 * Smart Post Show Help Page.
 */
class SPS_Premium {

	/**
	 * Smart Post Show single instance of the class
	 *
	 * @var null The instance of the class.
	 * @since 2.0
	 *
	 * @return void
	 */
	protected static $instance = null;

	/**
	 * Main Smart_Post_Show_Help Instance
	 *
	 * @since 2.0
	 * @static
	 *
	 * @return self help instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add admin sub-menu.
	 *
	 * @return void
	 */
	public function premium_page_menu() {
		add_submenu_page(
			'edit.php?post_type=sp_post_carousel',
			__( 'Smart Post Show Premium', 'smart-post-show' ),
			__( 'Premium', 'smart-post-show' ),
			apply_filters( 'pcp_user_role_permission', 'manage_options' ),
			'pcp_premium',
			array(
				$this,
				'premium_page_callback',
			)
		);
	}

	/**
	 * Happy users.
	 *
	 * @param boolean $username
	 * @param array   $args
	 * @return void
	 */
	public function happy_users( $username = 'shapedplugin', $args = array() ) {
		if ( $username ) {
			$params = array(
				'timeout'   => 10,
				'sslverify' => false,
			);

			$raw = wp_remote_retrieve_body( wp_remote_get( 'http://wptally.com/api/' . $username, $params ) );
			$raw = json_decode( $raw, true );

			if ( array_key_exists( 'error', $raw ) ) {
				$data = array(
					'error' => $raw['error'],
				);
			} else {
				$data = $raw;
			}
		} else {
			$data = array(
				'error' => __( 'No data found!', 'smart-post-show' ),
			);
		}

		return $data;
	}

	/**
	 * Premium Page Callback
	 */
	public function premium_page_callback() {
		wp_enqueue_style( 'sp-sps__admin-premium', SP_PC_URL . 'admin/assets/css/premium-page.min.css', array(), SP_PC_VERSION );
		wp_enqueue_style( 'sp-sps__admin-premium-modal', SP_PC_URL . 'admin/assets/css/modal-video.min.css', array(), SP_PC_VERSION );
		wp_enqueue_script( 'sp-sps__admin-premium', SP_PC_URL . 'admin/assets/js/jquery-modal-video.min.js', array( 'jquery' ), SP_PC_VERSION, true );
		?>
		<div class="sp-smart-post-premium-page">
		<!-- Banner section start -->
		<section class="sp-sps__banner">
			<div class="sp-sps__container">
				<div class="row">
					<div class="sp-sps__col-xl-6">
						<div class="sp-sps__banner-content">
							<h2 class="sp-sps__font-30 main-color sp-sps__font-weight-500"><?php _e( 'Upgrade To Smart Post Show Pro', 'smart-post-show' ); ?></h2>
							<h4 class="sp-sps__mt-10 sp-sps__font-18 sp-sps__font-weight-500"><?php _e( 'The Best <strong>Post Grid and Filter</strong> Plugin For WordPress!', 'smart-post-show' ); ?> </h4>
							<p class="sp-sps__mt-25 text-color-2 line-height-20 sp-sps__font-weight-400"><?php _e( ' Filter and display <strong>posts (any post type), pages, taxonomy, custom taxonomy, custom field,</strong> in beautiful layouts <strong>(carousel, grid, list, filter, timeline, zigzag, accordion, large with small)</strong> easily without coding!', 'smart-post-show' ); ?></p>
							<p class="sp-sps__mt-20 text-color-2 sp-sps__line-height-20 sp-sps__font-weight-400"><?php _e( 'You can select one, multiple, or all types of post content <strong>(posts, pages, products, events, portfolios, services, properties, and any custom post types)</strong>. Seamlessly work with many WordPress plugins: custom post, custom field, membership, translation, & page builders and supports <strong>ACF, WooCommerce, EDD, CPT UI, The Events Calendar, Events Manager, Pods, Toolset, WPML, Polylang, Membership,</strong> and many more.', 'smart-post-show' ); ?></p>
							
						</div>
						<div class="sp-sps__banner-button sp-sps__mt-40">
							<a class="sp-sps__btn sp-sps__btn-sky" href="https://smartpostshow.com/?ref=1" target="_blank">Upgrade To Pro Now</a>
							<a class="sp-sps__btn sp-sps__btn-border ml-16 sp-sps__mt-15" href="https://smartpostshow.com/demo/?ref=1" target="_blank">Live Demo</a>
						</div>
					</div>
					<div class="sp-sps__col-xl-6">
						<div class="sp-sps__banner-img">
							<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/sps-vect.svg'; ?>" alt="">
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Banner section End -->

		<!-- Count section Start -->
		<section class="sp-sps__count">
			<div class="sp-sps__container">
				<div class="sp-sps__count-area">
					<div class="count-item">
						<h3 class="sp-sps__font-24">
						<?php
						$plugin_data  = $this->happy_users();
						$plugin_names = array_values( $plugin_data['plugins'] );

						$active_installations = array_column( $plugin_names, 'installs', 'url' );
						echo esc_attr( $active_installations['http://wordpress.org/plugins/post-carousel'] ) . '+';
						?>
						</h3>
						<span class="sp-sps__font-weight-400">Active Installations</span>
					</div>
					<div class="count-item">
						<h3 class="sp-sps__font-24">
						<?php
						$active_installations = array_column( $plugin_names, 'downloads', 'url' );
						echo esc_attr( $active_installations['http://wordpress.org/plugins/post-carousel'] );
						?>
						</h3>
						<span class="sp-sps__font-weight-400">all time downloads</span>
					</div>
					<div class="count-item">
						<h3 class="sp-sps__font-24">
						<?php
						$active_installations = array_column( $plugin_names, 'rating', 'url' );
						echo esc_attr( $active_installations['http://wordpress.org/plugins/post-carousel'] ) . '/5';
						?>
						</h3>
						<span class="sp-sps__font-weight-400">user reviews</span>
					</div>
				</div>
			</div>
		</section>
		<!-- Count section End -->

		<!-- Video Section Start -->
		<section class="sp-sps__video">
			<div class="sp-sps__container">
				<div class="section-title text-center">
					<h2 class="sp-sps__font-28">Make Your WordPress Content Stand Out</h2>
					<h4 class="sp-sps__font-16 sp-sps__mt-10 sp-sps__font-weight-400">Learn why Smart Post Show Pro is the best WordPress Content display plugin.</h4>
				</div>
				<div class="video-area text-center">
					<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/wpcp-vector-2.svg'; ?>" alt="">
					<div class="video-button">
						<a class="js-video-button" href="#" data-channel="youtube" data-video-url="//www.youtube.com/embed/R72bq4McAw0">
							<span><i class="fa fa-play"></i></span>
						</a>
					</div>
				</div>
			</div>
		</section>
		<!-- Video Section End -->

		<!-- Features Section Start -->
		<section class="sp-sps__feature">
			<div class="sp-sps__container">
				<div class="section-title text-center">
					<h2 class="sp-sps__font-28">Amazing Pro Key Features</h2>
					<h4 class="sp-sps__font-16 sp-sps__mt-10 sp-sps__font-weight-400">With Smart Post Show Pro, you can unlock the following amazing features:</h4>
				</div>
				<div class="feature-wrapper">
					<div class="feature-area">
						<div class="feature-item mr-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/layout.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">8+ Smart Layout Presets</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">Shuffle through 8+ smart layouts. Smart Post Show Pro comes with 8 unique layouts <strong>(Carousel, Grid (Even, Masonry), List, Isotope, Timeline, ZigZag, Accordion, Large with Small)</strong>. to display posts and customizes the layout with several options.</p>
							</div>
						</div>

						<div class="feature-item ml-30">
							<div class="feature-icon">
								<img src="
								<?php echo SP_PC_URL . 'admin/assets/img/images/premium/advanced-filtering.svg'; ?>
								" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Advanced Filtering Options by Query Post</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">Smart Post Show Pro allows you to filter and display posts <strong>(any custom post types), pages, taxonomy, custom taxonomy, custom field, in different beautiful layouts. Filter by: taxonomy, author, sort by, custom fields, status, date, keyword,</strong>, etc.</p>
							</div>
						</div>
					</div>
					<div class="feature-area">
					<div class="feature-item mr-30">
							<div class="feature-icon">
								<img src="
								<?php echo SP_PC_URL . 'admin/assets/img/images/premium/Content.svg'; ?>
								" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Display Any WordPress Content Smartly</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">You can display any WordPress content smartly in minutes, not hours. If you have more content on your website, this is very difficult and time-consuming to find and show the content to your visitors. This plugin helps you to find and display any WordPress content easily.</p>
							</div>
						</div>
						<div class="feature-item ml-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/control.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Control Which Content Fields To Show</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">You can control which content fields you want to display. You can show/hide any content fields and change the display order of content by drag & drop. There are lots of settings to customize the following content fields: Thumbnails, Title, Meta Fields, Content, Social Share, Custom Fields, etc.</p>
							</div>
						</div>
					</div>
					<div class="feature-area">
					<div class="feature-item mr-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/orientation.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">6 Content Orientations with 8 Positions</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">Display any WordPress content smartly in 6 content orientations and even 8 positions. You can set 30+ animations for overlay content, solid and gradient overlay color, visibility type for overlay content, Border, radius, box-shadow, inner padding, etc.</p>
							</div>
						</div>
						<div class="feature-item ml-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/Custom-Post-Type.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Display Any Custom Post Type</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">You can select one, multiple, or all types of post content <strong>(posts, pages, products, events, portfolios, services, properties, and any custom post types)</strong>. Smart Post Show Pro has the easiest user interface for any level of people.</p>
							</div>
						</div>
					</div>
					<div class="feature-area">
					<div class="feature-item mr-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/Find.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Help Your Visitors To Find What They Want</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">It is important for any website owner to help visitors to find their desired content quickly. You can live search or multiple filters at a time. Find your content by many criteria: Taxonomies, Custom taxonomy and custom fields, date, author, status, keyword, sort by, etc.</p>
							</div>
						</div>
						<div class="feature-item ml-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/ajax.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">4 Ajax-based Pagination Types</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">Ajax Number, Ajax Load More Button, Ajax Infinite to Scroll, and Normal Pagination, You can control the number of items how many you want to load per click. You can set pagination types for the mobile, color, label, ending a message, alignment.</p>
							</div>
						</div>
					</div>
					<div class="feature-area">
					<div class="feature-item mr-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/Detail.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Detail Page Fields Control</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">You can select which content fields you want to show in the single and multi popup preview. You can set detail page link type, popup width & height, popup content, overlay, background colors, and show/Hide all content fields in the popup.</p>
							</div>
						</div>
						<div class="feature-item ml-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/design.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Design Beyond Limit Without Writing CSS</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">
								Change the design through a range of options without coding knowledge. You can set countless colors and hover colors, 950+ Google fonts, font-size, weight, & line-height, control typography on mobile devices, and much more customizable options.</p>
							</div>
						</div>
					</div>
					<div class="feature-area">
						<div class="feature-item mr-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/page-bilder.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Page Builders & Countless Theme Compatibility</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">The plugin works seamlessly with the popular themes, e.g: Avada, Genesis, Divi, WooThemes, ThemeForest, or any standard themes and page builders plugins, e.g: Gutenberg, WPBakery, Elementor/Pro, Divi builder, BeaverBuilder, Fusion Builder, SiteOrgin, Themify Builder, etc.</p>
							</div>
						</div>
						<div class="feature-item ml-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/Plugins.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Seamlessly Work With Many WordPress Plugins</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">Smart Post Show Pro works perfectly with many kinds of plugins: custom post, custom field, membership, translation, & page builders and supports ACF, WooCommerce, EDD, CPT UI, The Events Calendar, Events Manager, Pods, Toolset, WPML, Polylang, Membership, and many more.</p>
							</div>
						</div>
					</div>
					<div class="feature-area">
						<div class="feature-item mr-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/Translation-RTL-Ready.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Multisite, Multilingual, RTL, Accessibility Ready</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">The plugin is Multisite, Multilingual, RTL, and Accessibility ready. For Arabic, Hebrew, Persian, etc. languages, you can select the right-to-left option for carousel direction, without writing any CSS. You can even create multilingual websites.</p>
							</div>
						</div>
						<div class="feature-item ml-30">
							<div class="feature-icon">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/premium/support.svg'; ?>" alt="">
							</div>
							<div class="feature-content">
								<h3 class="sp-sps__font-18 sp-sps__font-weight-600">Top-notch Support and Frequently Updates</h3>
								<p class="sp-sps__font-15 sp-sps__mt-15 sp-sps__line-height-24">Our dedicated top-notch support team is always ready to offer you world-class support and help when needed. Our engineering team is continuously working to improve the plugin and release new versions!</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Features Section End -->

		<!-- Buy Section Start -->
		<section class="sp-sps__buy">
			<div class="sp-sps__container">
				<div class="row">
					<div class="sp-sps__col-xl-12">
						<div class="buy-content text-center">
							<h2 class="sp-sps__font-28">Join 
							<?php
							$install = 0;
							foreach ( $plugin_names as &$plugin_name ) {
								$install += $plugin_name['installs'];
							}
							echo esc_attr( $install + '15000' ) . '+';
							?>
							 Happy Users in 160+ Countries </h2>
							<p class="sp-sps__font-16 sp-sps__mt-25 sp-sps__line-height-22">98% of customers are happy with <b>ShapedPlugin's</b> products and support. <br>
								So it’s a great time to join them.</p>
							<a class="sp-sps__btn sp-sps__btn-buy sp-sps__mt-40" href="https://smartpostshow.com/?ref=1" target="_blank">Get Started for $39 Today!</a>
							<span>14 Days Money-back Guarantee! No Question Asked.</span>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Buy Section End -->

		<!-- Testimonial section start -->
		<div class="testimonial-wrapper">
		<section class="sp-sps__premium testimonial">
			<div class="row">
				<div class="col-lg-6">
					<div class="testimonial-area">
						<div class="testimonial-content">
							<p>A fantastic plugin with lots of options to style. The best thing, however, is the outstanding customer service form ShapedPlugin support team. Any question I had so far has been answered very promptly – mostly on the same day.</p>
						</div>
						<div class="testimonial-info">
							<div class="img">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/rainer-ott-min.jpeg'; ?>" alt="">
							</div>
							<div class="info">
								<h3>Rainer Ott</h3>
								<div class="star">
								<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="testimonial-area">
						<div class="testimonial-content">
							<p>I experienced a few issues with my Smart Post Show plugin and was connected to Hares from ShapedPlugin Support. He did a PHENOMENAL job of explaining the different features and benefits of Smart Post Show and answered all of my questions in a timely fashion...</p>
						</div>
						<div class="testimonial-info">
							<div class="img">
								<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/kato-von-essen-min.jpeg'; ?>" alt="">
							</div>
							<div class="info">
								<h3>Kato von Essen</h3>
								<div class="star">
								<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		</div>
		<!-- Testimonial section end -->
	</div>
	<!-- End premium page -->
		<?php
	}
}
