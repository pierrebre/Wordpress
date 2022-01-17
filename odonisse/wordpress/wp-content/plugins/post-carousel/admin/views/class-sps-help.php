<?php
/**
 * The main class for Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin/assets/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.

/**
 * Smart Post Show Help Page.
 */
class SPS_Help {

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
	public function help_page_menu() {
		add_submenu_page(
			'edit.php?post_type=sp_post_carousel',
			__( 'Smart Post Show Help', 'smart-post-show' ),
			__( 'Help', 'smart-post-show' ),
			apply_filters( 'pcp_user_role_permission', 'manage_options' ),
			'pcp_help',
			array(
				$this,
				'help_page_callback',
			)
		);
	}

	/**
	 * Help Page Callback
	 */
	public function help_page_callback() {
		wp_enqueue_style( 'sp-sps__admin-help', SP_PC_URL . 'admin/assets/css/help-page.min.css', array(), SP_PC_VERSION );
		$add_new_carousel_link = admin_url( 'post-new.php?post_type=sp_post_carousel' );
		?>

		<div class="sp-smart-post__help-page">
		<!-- Header section start -->
		<section class="sp-sps__help header">
			<div class="header-area">
				<div class="container">
					<div class="header-logo">
						<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/smart-post-show-logo.svg'; ?>" alt="">
						<span><?php echo SP_PC_VERSION; ?></span>
					</div>
					<div class="header-content">
						<p>Thank you for installing Smart Post Show plugin! This video will help you get started with the plugin.</p>
					</div>
				</div>
			</div>
			<div class="video-area">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/875igMBtpMg" frameborder="0" allowfullscreen=""></iframe>
			</div>
			<div class="content-area">
				<div class="container">
					<div class="content-button">
						<a href="<?php echo esc_url( $add_new_carousel_link ); ?>">Start Adding Shows</a>
						<a href="https://docs.shapedplugin.com/docs/post-carousel/overview/?ref=1" target="_blank">Read Documentation</a>
					</div>
				</div>
			</div>
		</section>
		<!-- Header section end -->

		<!-- Upgrade section start -->
		<section class="sp-sps__help upgrade">
			<div class="upgrade-area">
				<div class="upgrade-img">
				<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/sps-icon-copy.svg'; ?>" alt="">
				</div>
				<h2>Upgrade To Unleash the Power of Smart Post Show Pro</h2>
				<p>Get the most out of Smart Post Show by upgrading to unlock all of its powerful features. With Smart Post Show Pro, you can unlock amazing features like:</p>
			</div>
			<div class="upgrade-info">
				<div class="container">
					<div class="row">
						<div class="col-lg-6">
							<ul class="upgrade-list">
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">8+ Smart Layout Presets.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Filter and display custom post type, custom taxonomy, custom fields.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Live front-end filtering, search, and sort (by category, tag, custom taxonomy, custom field, keyword search) for any post type.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Display multiple post types in one show/shortcode (e.g: display posts, pages, products, portfolio in a layout).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Turn any layout into an isotope(shuffle) filter.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Even and masonry for the grid.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt=""> Ajax number, Load more, Infinite scrolling pagination.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Filter and display Woocommerce products (recent, on sale, best-selling, featured, top-rated, out of stock, many more, etc.).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Supports ACF, WooCommerce, EDD, CPT UI, The Events Calendar, Events Manager, Pods, Toolset, WPML, Polylang, Membership, and many more.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Isotope(shuffle) filter posts by taxonomy terms.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">The search field on the isotope or any layout.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Content excerpt, limit, and full content display option.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">12 Social sharing media, alignment, margin. icon shape, custom color, and many more.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">30+ Carousel controls for carousel layout with numerous options.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Sticky posts (normal position, top of the list, hide sticky posts).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Include (select specific posts), exclude post or page by ID, title (enter post IDs, or type to search by title).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Offset options (number of items to skip).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Filter by a category, tags, author, status, keyword, etc.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Filter by custom taxonomy, custom fields.</li>	
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Filter by date (today, future, custom date, custom year, specific: date, month, year; specific period(from & to), many more, etc).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Sort by ID, Title, Date, Modified date, post in (drag & drop), post slug, post type, random, custom field, comment count, page order (menu order), author, most liked, most viewed.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">6 Content orientations with 8 positions.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">The margin for every content field (thumbnail, title, meta fields, content, social share, custom fields).</li>
							</ul>
						</div>
						<div class="col-lg-6">
							<ul class="upgrade-list">
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">30+ Animation types for the overlay content on hover.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Overlay content visibility: always & on hover and overlay color type (solid & gradient).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Thumbnail show/hide, available sizes, custom size with soft & hard cropping, margin, image source, border, border-radius, zoom effect, image mode (grayscale).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Title show/hide, title HTML tag select, title character limit, margin, etc.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Meta icons (all font-awesome icons available) and meta separator options (normal space, full stop, straight line, slash, backslash).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Select which taxonomy to show in meta fields and set position: beside other meta, above title, below the title.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Content show/hide, content excerpt, an excerpt with limit, full content, HTML tags (allow all, strip all, allow some).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Exclude content of specific HTML tags in the excerpt.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Live filter options (filter type: dropdown, radion, button; label, hide empty terms, show post count, alignment, etc.).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Set custom size (custom width, custom height) for thumbnail.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Image mode (grayscale), zoom effects.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Popup Preview (Single popup and multi popup).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Carousel mode (standard, center, ticker).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Navigation show/hide, 8 positions, arrow icon size, color, and hover color.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Detail page link type (popup, single page, none).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Popup type (single and multi popup preview with navigation).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Popup width & height and change popup content, overlay, background color.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Show/Hide all content fields in the popup.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Typography Settings (font family, weight, margin, text-align, text-transform, letter spacing, colors, etc.).</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Automatic update through activated license key.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt="">Top-notch One to One support from the expert engineers.</li>
								<li><img src="<?php echo SP_PC_URL . 'admin/assets/img/images/checkmark.svg'; ?>" alt=""><span>Not Happy? 100% No Questions Asked <a href="https://shapedplugin.com/refund-policy/" target="_blank">Refund Policy!</a></span></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="container">
				<div class="upgrade-pro">
					<div class="pro-content">
						<div class="pro-icon">
							<img src="<?php echo SP_PC_URL . 'admin/assets/img/images/sps-icon.svg'; ?>" alt="">
						</div>
						<div class="pro-text">
							<h2>Upgrade To Smart Post Show Pro</h2>
							<p>Start displaying your any WordPress Content in Minutes!</p>
						</div>
					</div>
					<div class="pro-btn">
						<a href="https://smartpostshow.com/?ref=1" target="_blank">Upgrade To Pro Now</a>
					</div>
				</div>
			</div>
		</section>
		<!-- Upgrade section end -->

		<!-- Testimonial section start -->
		<section class="sp-sps__help testimonial">
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
		<!-- Testimonial section end -->

	</div>
		<?php
	}
}

