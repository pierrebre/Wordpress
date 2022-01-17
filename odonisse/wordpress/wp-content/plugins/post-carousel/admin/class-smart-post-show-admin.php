<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link        https://smartpostshow.com/
 * @since      2.2.0
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 * @author     ShapedPlugin <support@shapedplugin.com>
 */
class Smart_Post_Show_Admin {

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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Autoload system.
		spl_autoload_register( array( $this, 'autoload' ) );

		SPS_Metaboxes::layout_metabox( 'sp_pcp_layouts' );
		SPS_Metaboxes::option_metabox( 'sp_pcp_view_options' );
		SPS_Metaboxes::shortcode_metabox( 'sp_pcp_display_shortcode' );
		SPS_Settings::settings( 'sp_post_carousel_settings' );
		// Plugin action link Button.
		add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 2 );
	}
	/**
	 * Add plugin action menu
	 *
	 * @param array $links The action link.
	 *
	 * @return array
	 */
	public function add_plugin_action_links( $links, $file ) {
		if ( SMART_POST_SHOW_BASENAME === $file ) {
			$new_links       = array(
				sprintf( '<a href="%s">%s</a>', admin_url( 'post-new.php?post_type=sp_post_carousel' ), __( 'Add New', 'smart-post-show' ) ),
				sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=sp_post_carousel&page=pcp_settings' ), __( 'Settings', 'smart-post-show' ) ),
			);
			$links['go_pro'] = sprintf( '<a href="%s" style="%s">%s</a>', 'https://smartpostshow.com/', 'color:#1dab87;font-weight:bold', __( 'Go Premium!', 'smart-post-show' ) );
			return array_merge( $new_links, $links );
		}
		return $links;
	}
	/**
	 *  Add plugin row meta link
	 *
	 * @param [array] $plugin_meta Add plugin row meta link.
	 * @param [url]   $file plugin row meta link.
	 * @return array
	 */
	public function after_pcp_row_meta( $plugin_meta, $file ) {
		if ( SMART_POST_SHOW_BASENAME == $file ) {
			$plugin_meta[] = '<a href="https://smartpostshow.com/demo/" target="_blank">' . __( 'Live Demo', 'smart-post-show' ) . '</a>';
			$plugin_meta[] = '<a href="https://docs.shapedplugin.com/docs/post-carousel/overview/" target="_blank">' . __( 'Documentation', 'smart-post-show' ) . '</a>';
			$plugin_meta[] = '<a href="https://shapedplugin.com/support/?user=lite" target="_blank">' . __( 'Support', 'smart-post-show' ) . '</a>';
		}
		return $plugin_meta;
	}
	/**
	 * Autoload class files on demand
	 *
	 * @param string $class requested class name.
	 * @since 2.2.0
	 */
	private function autoload( $class ) {
		$name = explode( '_', $class );
		if ( isset( $name[1] ) ) {
			$class_name       = strtolower( $name[1] );
			$pcp_config_paths = array( 'views/', 'views/configs/settings/', 'views/configs/generator/' );
			foreach ( $pcp_config_paths as $sp_ppc_path ) {
				$filename = plugin_dir_path( __FILE__ ) . '/' . $sp_ppc_path . 'class-sps-' . $class_name . '.php';
				if ( file_exists( $filename ) ) {
					require_once $filename;
				}
			}
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.2.0
	 */
	public function enqueue_styles() {
		$current_screen        = get_current_screen();
		$the_current_post_type = $current_screen->post_type;
		if ( 'sp_post_carousel' === $the_current_post_type ) {
			wp_enqueue_style( 'pcp-font-awesome', SP_PC_URL . 'public/assets/css/font-awesome.min.css', array(), SP_PC_VERSION, 'all' );
		}
		wp_enqueue_style( 'sp-' . SP_PC_PLUGIN_NAME, SP_PC_URL . 'admin/assets/css/post-carousel-admin' . $this->suffix . '.css', array(), SP_PC_VERSION, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.2.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smart_Post_Show_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smart_Post_Show_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$current_screen        = get_current_screen();
		$the_current_post_type = $current_screen->post_type;
		if ( 'sp_post_carousel' === $the_current_post_type ) {
			wp_enqueue_script( SP_PC_PLUGIN_NAME, SP_PC_URL . 'admin/assets/js/post-carousel-admin' . $this->suffix . '.js', array( 'jquery' ), SP_PC_VERSION, true );
		}

	}

	/**
	 * Add carousel admin columns.
	 *
	 * @since 2.2.0
	 * @return statement
	 */
	public function filter_carousel_admin_column() {
		$admin_columns['cb']         = '<input type="checkbox" />';
		$admin_columns['title']      = __( 'Title', 'smart-post-show' );
		$admin_columns['shortcode']  = __( 'Shortcode', 'smart-post-show' );
		$admin_columns['pcp_layout'] = __( 'Layout', 'smart-post-show' );
		$admin_columns['date']       = __( 'Date', 'smart-post-show' );

		return $admin_columns;
	}

	/**
	 * Display admin columns for the carousels.
	 *
	 * @param mix    $column The columns.
	 * @param string $post_id The post ID.
	 * @return void
	 */
	public function display_carousel_admin_fields( $column, $post_id ) {
		$pcp_layouts     = get_post_meta( $post_id, 'sp_pcp_layouts', true );
		$carousels_types = isset( $pcp_layouts['pcp_layout_preset'] ) ? $pcp_layouts['pcp_layout_preset'] : '';
		switch ( $column ) {
			case 'shortcode':
				$column_field = '<input  class="sp_pcp_input" style="width: 230px;padding: 4px 8px;cursor: pointer;" type="text" onClick="this.select();" readonly="readonly" value="[smart_post_show id=&quot;' . $post_id . '&quot;]"/> <div class="pcp-after-copy-text"><i class="fa fa-check-circle"></i> Shortcode Copied to Clipboard! </div>';
				echo $column_field;
				break;
			case 'pcp_layout':
				$layout = ucwords( str_replace( '_layout', ' ', $carousels_types ) );
				esc_html_e( $layout, 'smart-post-show' );
		} // end switch.
	}

	/**
	 *  Sp_post_carousel post type Save and update alert in Admin Dashboard created by Post carousel Pro
	 *
	 * @param array $messages alert messages.
	 */
	public function sppcp_update( $messages ) {
		global $post, $post_ID;
		$messages['sp_post_carousel'][1] = __( 'Shortcode Updated', 'smart-post-show' );
		$messages['sp_post_carousel'][6] = __( 'Shortcode Published', 'smart-post-show' );
		return $messages;
	}


	/**
	 * If it is the plugins page.
	 *
	 * @since 2.2.0
	 * @access private
	 */
	private function is_plugins_screen() {
		return in_array( get_current_screen()->id, array( 'plugins', 'plugins-network' ) );
	}
}

/**
 * Smart Post Show dashboard capability.
 *
 * @return string
 */
function sp_pc_dashboard_capability() {
	return apply_filters( 'sp_pc_dashboard_capability', 'manage_options' );
}
