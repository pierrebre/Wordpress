<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link        https://smartpostshow.com/
 * @since      2.2.0
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/includes
 */

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
class Smart_Post_Show {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.2.0
	 * @access   protected
	 * @var      Smart_Post_Show_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	public $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.2.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	public $plugin_name = SMART_POST_SHOW_BASENAME;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.2.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	public $version = SMART_POST_SHOW_VERSION;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.2.0
	 */
	public function __construct() {
		$this->define_constants();
		$this->load_dependencies();
		$this->define_common_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->set_locale();
	}
	/**
	 * Define constant if not already set
	 *
	 * @since 2.2.0
	 *
	 * @param string      $name Define constant.
	 * @param string|bool $value Define constant.
	 */
	public function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Define constants
	 *
	 * @since 2.2.0
	 */
	public function define_constants() {
		$this->define( 'SP_PC_VERSION', $this->version );
		$this->define( 'SP_PC_PLUGIN_NAME', $this->plugin_name );
		$this->define( 'SP_PC_PATH', plugin_dir_path( dirname( __FILE__ ) ) );
		$this->define( 'SP_PC_TEMPLATE_PATH', plugin_dir_path( dirname( __FILE__ ) ) . 'public/template/' );
		$this->define( 'SP_PC_URL', plugin_dir_url( dirname( __FILE__ ) ) );
		$this->define( 'SMART_POST_SHOW_BASENAME', SMART_POST_SHOW_BASENAME );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smart_Post_Show_Loader. Orchestrates the hooks of the plugin.
	 * - Smart_Post_Show_i18n. Defines internationalization functionality.
	 * - Smart_Post_Show_Admin. Defines all hooks for the admin area.
	 * - Smart_Post_Show_Public. Defines all hooks for the public side of the site.
	 * - Smart_Post_Show_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.2.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SP_PC_PATH . 'includes/class-smart-post-show-loader.php';
		$this->loader = new Smart_Post_Show_Loader();

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SP_PC_PATH . 'includes/class-smart-post-show-i18n.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SP_PC_PATH . 'includes/class-smart-post-show-post-types.php';

		/**
		 * The class responsible for updates functionality of the plugin.
		 */
		require_once SP_PC_PATH . 'includes/class-smart-post-show-updates.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SP_PC_PATH . 'admin/class-smart-post-show-admin.php';

		/**
		 * The class responsible for defining metabox setup that occur in the admin area.
		 */
		require_once SP_PC_PATH . '/admin/views/sp-framework/classes/setup.class.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SP_PC_PATH . 'public/class-smart-post-show-public.php';

		/**
		 * The class responsible for review notice.
		 */
		require_once SP_PC_PATH . 'admin/views/notices/review.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smart_Post_Show_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.2.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Smart_Post_Show_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register common hooks.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	private function define_common_hooks() {
		$common_hooks = new Smart_Post_Show_Post_Type( SP_PC_PLUGIN_NAME, SP_PC_VERSION );
		$this->loader->add_action( 'init', $common_hooks, 'register_carousel_post_type', 10 );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.2.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Smart_Post_Show_Admin( SP_PC_PLUGIN_NAME, SP_PC_VERSION );
		// Load admin scripts and styles.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// Admin custom column.
		$this->loader->add_filter( 'manage_sp_post_carousel_posts_columns', $plugin_admin, 'filter_carousel_admin_column' );
		$this->loader->add_action( 'manage_sp_post_carousel_posts_custom_column', $plugin_admin, 'display_carousel_admin_fields', 10, 2 );

		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'after_pcp_row_meta', 10, 4 );
		// Post save and update messages.
		$this->loader->add_filter( 'post_updated_messages', $plugin_admin, 'sppcp_update', 10, 1 );
		// Help Page.
		$help_menu = new SPS_Help();
		$this->loader->add_action( 'admin_menu', $help_menu, 'help_page_menu', 25 );
		// Premium Page.
		$premium_menu = new SPS_Premium();
		$this->loader->add_action( 'admin_menu', $premium_menu, 'premium_page_menu', 20 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.2.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Smart_Post_Show_Public( SP_PC_PLUGIN_NAME, SP_PC_VERSION );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.2.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.2.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.2.0
	 * @return    Smart_Post_Show_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.2.0
	 * @return    string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
