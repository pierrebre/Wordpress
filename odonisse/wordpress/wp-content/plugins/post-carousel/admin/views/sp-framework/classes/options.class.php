<?php if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.
/**
 *
 * Options Class
 *
 * @since 1.0.0
 * @version 1.0.0
 */
if ( ! class_exists( 'SP_PC_Options' ) ) {
	class SP_PC_Options extends SP_PC_Abstract {

		// constans.
		public $unique       = '';
		public $notice       = '';
		public $abstract     = 'options';
		public $sections     = array();
		public $options      = array();
		public $errors       = array();
		public $pre_tabs     = array();
		public $pre_fields   = array();
		public $pre_sections = array();

		// default args.
		public $args = array(

			// framework title.
			'framework_title'         => 'SPF Framework <small>by SP</small>',
			'framework_class'         => '',

			// menu settings.
			'menu_title'              => '',
			'menu_slug'               => '',
			'menu_type'               => 'menu',
			'menu_capability'         => 'manage_options',
			'menu_icon'               => null,
			'menu_position'           => null,
			'menu_hidden'             => false,
			'menu_parent'             => '',

			// menu extras.
			'show_bar_menu'           => true,
			'show_sub_menu'           => true,
			'show_network_menu'       => true,
			'show_in_customizer'      => false,

			'show_search'             => true,
			'show_reset_all'          => true,
			'show_reset_section'      => true,
			'show_footer'             => true,
			'show_all_options'        => true,
			'sticky_header'           => true,
			'save_defaults'           => true,
			'ajax_save'               => true,

			// admin bar menu settings.
			'admin_bar_menu_icon'     => '',
			'admin_bar_menu_priority' => 80,

			// footer.
			'footer_text'             => '',
			'footer_after'            => '',
			'footer_credit'           => '',

			// database model.
			'database'                => '', // options, transient, theme_mod, network.
			'transient_time'          => 0,

			// contextual help.
			'contextual_help'         => array(),
			'contextual_help_sidebar' => '',

			// typography options.
			'enqueue_webfont'         => true,
			'async_webfont'           => false,

			// others.
			'output_css'              => true,

			// theme.
			'theme'                   => 'dark',

			'class'                   => '',

			// external default values.
			'defaults'                => array(),

		);

		// run framework construct.
		public function __construct( $key, $params = array() ) {

			$this->unique   = $key;
			$this->args     = apply_filters( "spf_{$this->unique}_args", wp_parse_args( $params['args'], $this->args ), $this );
			$this->sections = apply_filters( "spf_{$this->unique}_sections", $params['sections'], $this );

			// run only is admin panel options, avoid performance loss.
			$this->pre_tabs     = $this->pre_tabs( $this->sections );
			$this->pre_fields   = $this->pre_fields( $this->sections );
			$this->pre_sections = $this->pre_sections( $this->sections );

			$this->get_options();
			$this->set_options();
			$this->save_defaults();

			add_action( 'admin_menu', array( &$this, 'add_admin_menu' ) );
			add_action( 'admin_bar_menu', array( &$this, 'add_admin_bar_menu' ), $this->args['admin_bar_menu_priority'] );
			add_action( 'wp_ajax_spf_' . $this->unique . '_ajax_save', array( &$this, 'ajax_save' ) );

			if ( ! empty( $this->args['show_network_menu'] ) ) {
				add_action( 'network_admin_menu', array( &$this, 'add_admin_menu' ) );
			}

			// wp enqeueu for typography and output css.
			parent::__construct();

		}

		// instance.
		public static function instance( $key, $params = array() ) {
			return new self( $key, $params );
		}

		public function pre_tabs( $sections ) {

			$result  = array();
			$parents = array();
			$count   = 100;

			foreach ( $sections as $key => $section ) {
				if ( ! empty( $section['parent'] ) ) {
					$section['priority']             = ( isset( $section['priority'] ) ) ? $section['priority'] : $count;
					$parents[ $section['parent'] ][] = $section;
					unset( $sections[ $key ] );
				}
				$count++;
			}

			foreach ( $sections as $key => $section ) {
				$section['priority'] = ( isset( $section['priority'] ) ) ? $section['priority'] : $count;
				if ( ! empty( $section['id'] ) && ! empty( $parents[ $section['id'] ] ) ) {
					$section['subs'] = wp_list_sort( $parents[ $section['id'] ], array( 'priority' => 'ASC' ), 'ASC', true );
				}
				$result[] = $section;
				$count++;
			}

			return wp_list_sort( $result, array( 'priority' => 'ASC' ), 'ASC', true );
		}

		public function pre_fields( $sections ) {

			$result = array();

			foreach ( $sections as $key => $section ) {
				if ( ! empty( $section['fields'] ) ) {
					foreach ( $section['fields'] as $field ) {
						$result[] = $field;
					}
				}
			}

			return $result;
		}

		public function pre_sections( $sections ) {

			$result = array();

			foreach ( $this->pre_tabs as $tab ) {
				if ( ! empty( $tab['subs'] ) ) {
					foreach ( $tab['subs'] as $sub ) {
						$result[] = $sub;
					}
				}
				if ( empty( $tab['subs'] ) ) {
					$result[] = $tab;
				}
			}

			return $result;
		}

		// add admin bar menu.
		public function add_admin_bar_menu( $wp_admin_bar ) {

			if ( ! empty( $this->args['show_bar_menu'] ) && empty( $this->args['menu_hidden'] ) ) {

				global $submenu;

				$menu_slug = $this->args['menu_slug'];
				$menu_icon = ( ! empty( $this->args['admin_bar_menu_icon'] ) ) ? '<span class="spf-ab-icon ab-icon ' . $this->args['admin_bar_menu_icon'] . '"></span>' : '';

				$wp_admin_bar->add_node(
					array(
						'id'    => $menu_slug,
						'title' => $menu_icon . $this->args['menu_title'],
						'href'  => ( is_network_admin() ) ? network_admin_url( 'admin.php?page=' . $menu_slug ) : admin_url( 'admin.php?page=' . $menu_slug ),
					)
				);

				if ( ! empty( $submenu[ $menu_slug ] ) ) {
					foreach ( $submenu[ $menu_slug ] as $key => $menu ) {
						$wp_admin_bar->add_node(
							array(
								'parent' => $menu_slug,
								'id'     => $menu_slug . '-' . $key,
								'title'  => $menu[0],
								'href'   => ( is_network_admin() ) ? network_admin_url( 'admin.php?page=' . $menu[2] ) : admin_url( 'admin.php?page=' . $menu[2] ),
							)
						);
					}
				}

				if ( ! empty( $this->args['show_network_menu'] ) ) {
					$wp_admin_bar->add_node(
						array(
							'parent' => 'network-admin',
							'id'     => $menu_slug . '-network-admin',
							'title'  => $menu_icon . $this->args['menu_title'],
							'href'   => network_admin_url( 'admin.php?page=' . $menu_slug ),
						)
					);
				}
			}

		}

		public function ajax_save() {

			if ( ! empty( $_POST['data'] ) ) {

				$_POST = json_decode( stripslashes( $_POST['data'] ), true );

				// if ( ! empty( $_POST['spf_options_nonce'] ) && wp_verify_nonce( $_POST['spf_options_nonce'], 'spf_options_nonce' ) ) {
				if ( wp_verify_nonce( spf_get_var( 'spf_options_nonce' . $this->unique ), 'spf_options_nonce' ) ) {

					$this->set_options();

					wp_send_json_success(
						array(
							'success' => true,
							'notice'  => $this->notice,
							'errors'  => $this->errors,
						)
					);

				}
			}

			wp_send_json_error(
				array(
					'success' => false,
					'error'   => esc_html__(
						'Error while saving.',
						'smart-post-show'
					),
				)
			);

		}

		// get default value
		public function get_default( $field, $options = array() ) {

			$default = ( isset( $this->args['defaults'][ $field['id'] ] ) ) ? $this->args['defaults'][ $field['id'] ] : '';
			$default = ( isset( $field['default'] ) ) ? $field['default'] : $default;
			$default = ( isset( $options[ $field['id'] ] ) ) ? $options[ $field['id'] ] : $default;

			return $default;

		}

		// save defaults and set new fields value to main options.
		public function save_defaults() {

			$tmp_options = $this->options;

			foreach ( $this->pre_fields as $field ) {
				if ( ! empty( $field['id'] ) ) {
					$this->options[ $field['id'] ] = $this->get_default( $field, $this->options );
				}
			}

			if ( $this->args['save_defaults'] && empty( $tmp_options ) ) {
				$this->save_options( $this->options );
			}

		}

		// set options.
		public function set_options() {

			if ( wp_verify_nonce( spf_get_var( 'spf_options_nonce' . $this->unique ), 'spf_options_nonce' ) ) {

				$request    = spf_get_var( $this->unique, array() );
				$transient  = spf_get_var( 'spf_transient' );
				$section_id = ( ! empty( $transient['section'] ) ) ? $transient['section'] : '';

				// import data.
				if ( ! empty( $transient['spf_import_data'] ) ) {

					$import_data = json_decode( stripslashes( trim( $transient['spf_import_data'] ) ), true );
					$request     = ( is_array( $import_data ) ) ? $import_data : array();

					$this->notice = esc_html__( 'Success. Imported backup options.', 'smart-post-show' );

				} elseif ( ! empty( $transient['reset'] ) ) {

					foreach ( $this->pre_fields as $field ) {
						if ( ! empty( $field['id'] ) ) {
							$request[ $field['id'] ] = $this->get_default( $field );
						}
					}

					$this->notice = esc_html__( 'Default options restored.', 'smart-post-show' );

				} elseif ( ! empty( $transient['reset_section'] ) && ! empty( $section_id ) ) {

					if ( ! empty( $this->pre_sections[ $section_id - 1 ]['fields'] ) ) {

						foreach ( $this->pre_sections[ $section_id - 1 ]['fields'] as $field ) {
							if ( ! empty( $field['id'] ) ) {
								$request[ $field['id'] ] = $this->get_default( $field );
							}
						}
					}

					$this->notice = esc_html__( 'Default options restored for only this section.', 'smart-post-show' );

				} else {

					// sanitize and validate.
					foreach ( $this->pre_fields as $field ) {

						if ( ! empty( $field['id'] ) ) {

							// sanitize.
							if ( ! empty( $field['sanitize'] ) ) {

								$sanitize                = $field['sanitize'];
								$value_sanitize          = isset( $request[ $field['id'] ] ) ? $request[ $field['id'] ] : '';
								$request[ $field['id'] ] = call_user_func( $sanitize, $value_sanitize );

							}

							// validate.
							if ( ! empty( $field['validate'] ) ) {

								$value_validate = isset( $request[ $field['id'] ] ) ? $request[ $field['id'] ] : '';
								$has_validated  = call_user_func( $field['validate'], $value_validate );

								if ( ! empty( $has_validated ) ) {
									$request[ $field['id'] ]      = ( isset( $this->options[ $field['id'] ] ) ) ? $this->options[ $field['id'] ] : '';
									$this->errors[ $field['id'] ] = $has_validated;
								}
							}

							// auto sanitize.
							if ( ! isset( $request[ $field['id'] ] ) || is_null( $request[ $field['id'] ] ) ) {
								$request[ $field['id'] ] = '';
							}
						}
					}
				}

				// ignore nonce requests.
				if ( isset( $request['_nonce'] ) ) {
					unset( $request['_nonce'] ); }

				$request = wp_unslash( $request );

				$request = apply_filters( "spf_{$this->unique}_save", $request, $this );

				do_action( "spf_{$this->unique}_save_before", $request, $this );

				$this->options = $request;

				$this->save_options( $request );

				do_action( "spf_{$this->unique}_save_after", $request, $this );

				if ( empty( $this->notice ) ) {
					$this->notice = esc_html__( 'Settings saved.', 'smart-post-show' );
				}
			}

			return true;

		}

		// save options database.
		public function save_options( $request ) {

			if ( $this->args['database'] === 'transient' ) {
				set_transient( $this->unique, $request, $this->args['transient_time'] );
			} elseif ( $this->args['database'] === 'theme_mod' ) {
				set_theme_mod( $this->unique, $request );
			} elseif ( $this->args['database'] === 'network' ) {
				update_site_option( $this->unique, $request );
			} else {
				update_option( $this->unique, $request );
			}

			do_action( "spf_{$this->unique}_saved", $request, $this );

		}

		// get options from database.
		public function get_options() {

			if ( $this->args['database'] === 'transient' ) {
				$this->options = get_transient( $this->unique );
			} elseif ( $this->args['database'] === 'theme_mod' ) {
				$this->options = get_theme_mod( $this->unique );
			} elseif ( $this->args['database'] === 'network' ) {
				$this->options = get_site_option( $this->unique );
			} else {
				$this->options = get_option( $this->unique );
			}

			if ( empty( $this->options ) ) {
				$this->options = array();
			}

			return $this->options;

		}

		/**
		 * wp api: admin menu.
		 */
		public function add_admin_menu() {

			extract( $this->args );

			if ( $menu_type === 'submenu' ) {

				$menu_page = call_user_func( 'add_submenu_page', $menu_parent, $menu_title, $menu_title, $menu_capability, $menu_slug, array( &$this, 'add_options_html' ) );

			} else {

				$menu_page = call_user_func( 'add_menu_page', $menu_title, $menu_title, $menu_capability, $menu_slug, array( &$this, 'add_options_html' ), $menu_icon, $menu_position );

				if ( ! empty( $this->args['show_sub_menu'] ) && count( $this->pre_tabs ) > 1 ) {

					// create submenus.
					$tab_key = 1;
					foreach ( $this->pre_tabs as $section ) {

						call_user_func( 'add_submenu_page', $menu_slug, $section['title'], $section['title'], $menu_capability, $menu_slug . '#tab=' . $tab_key, '__return_null' );

						if ( ! empty( $section['subs'] ) ) {
							$tab_key += ( count( $section['subs'] ) - 1 );
						}

						$tab_key++;

					}

					remove_submenu_page( $menu_slug, $menu_slug );

				}

				if ( ! empty( $menu_hidden ) ) {
					remove_menu_page( $menu_slug );
				}
			}

			add_action( 'load-' . $menu_page, array( &$this, 'add_page_on_load' ) );

		}

		/**
		 * Add page on load.
		 */
		public function add_page_on_load() {

			if ( ! empty( $this->args['contextual_help'] ) ) {

				$screen = get_current_screen();

				foreach ( $this->args['contextual_help'] as $tab ) {
					$screen->add_help_tab( $tab );
				}

				if ( ! empty( $this->args['contextual_help_sidebar'] ) ) {
					$screen->set_help_sidebar( $this->args['contextual_help_sidebar'] );
				}
			}

		}

		/**
		 * Error check function.
		 */
		/**
		 * Error Check function
		 *
		 * @param mixed  $sections The section.
		 * @param string $err The error.
		 * @return statement
		 */
		public function error_check( $sections, $err = '' ) {

			if ( ! $this->args['ajax_save'] ) {

				if ( ! empty( $sections['fields'] ) ) {
					foreach ( $sections['fields'] as $field ) {
						if ( ! empty( $field['id'] ) ) {
							if ( array_key_exists( $field['id'], $this->errors ) ) {
								$err = '<span class="spf-label-error">!</span>';
							}
						}
					}
				}

				if ( ! empty( $sections['subs'] ) ) {
					foreach ( $sections['subs'] as $sub ) {
						$err = $this->error_check( $sub, $err );
					}
				}

				if ( ! empty( $sections['id'] ) && array_key_exists( $sections['id'], $this->errors ) ) {
					$err = $this->errors[ $sections['id'] ];
				}
			}

			return $err;
		}

		/**
		 * Option page html output.
		 *
		 * @return void
		 */
		public function add_options_html() {

			$has_nav       = ( count( $this->pre_tabs ) > 1 ) ? true : false;
			$show_all      = ( ! $has_nav ) ? ' spf-show-all' : '';
			$ajax_class    = ( $this->args['ajax_save'] ) ? ' spf-save-ajax' : '';
			$sticky_class  = ( $this->args['sticky_header'] ) ? ' spf-sticky-header' : '';
			$wrapper_class = ( $this->args['framework_class'] ) ? ' ' . $this->args['framework_class'] : '';
			$theme         = ( $this->args['theme'] ) ? ' spf-theme-' . $this->args['theme'] : '';
			$class         = ( $this->args['class'] ) ? ' ' . $this->args['class'] : '';

			echo '<div class="wrap"><h1>' . $this->args['menu_title'] . '</h1></div>';
			echo '<div class="spf spf-options' . $theme . $class . $wrapper_class . '" data-slug="' . $this->args['menu_slug'] . '" data-unique="' . $this->unique . '">';

			$notice_class = ( ! empty( $this->notice ) ) ? ' spf-form-show' : '';
			$notice_text  = ( ! empty( $this->notice ) ) ? $this->notice : '';

			echo '<div class="spf-form-result spf-form-success' . $notice_class . '">' . $notice_text . '</div>';

			$error_class = ( ! empty( $this->errors ) ) ? ' spf-form-show' : '';

			echo '<div class="spf-form-result spf-form-error' . $error_class . '">';
			if ( ! empty( $this->errors ) ) {
				foreach ( $this->errors as $error ) {
					echo '<i class="spf-label-error">!</i> ' . $error . '<br />';
				}
			}
			echo '</div>';

			echo '<div class="spf-container">';

			echo '<form method="post" action="" enctype="multipart/form-data" id="spf-form" autocomplete="off">';

			echo '<input type="hidden" class="spf-section-id" name="spf_transient[section]" value="1">';
			wp_nonce_field( 'spf_options_nonce', 'spf_options_nonce' . $this->unique );

			echo '<div class="spf-header' . esc_attr( $sticky_class ) . '">';
			echo '<div class="spf-header-inner">';

			echo '<div class="spf-header-left">';
			echo '<h1><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="1.4em" fill="#e1624b" height="1.4em" viewBox="0 0 288 288" enable-background="new 0 0 288 288" xml:space="preserve"><path fill="#e1624b" d="M262.102,20.977H27.615c-3.195,0-6.638,2.401-6.638,5.63v234.359c0,3.229,3.443,6.057,6.638,6.057h234.487c3.187,0,4.921-2.828,4.921-6.057V26.607C267.023,23.378,265.289,20.977,262.102,20.977z M118.37,53.441h51.26v43.571h-51.26V53.441z M55.15,53.441h51.26v43.571H55.15V53.441z M135.457,235.413H55.15v-46.134h80.307V235.413z M135.457,173.047H55.15v-46.134h80.307V173.047z M235.413,235.413h-80.307v-46.134h80.307V235.413z M235.413,173.047h-80.307v-46.134h80.307V173.047z M235.413,97.012h-51.26V53.441h51.26V97.012z"/><line fill="none" x1="-99" y1="-84" x2="-99" y2="-57"/><line fill="none" x1="-170" y1="61" x2="-170" y2="101"/></svg>' . esc_html__( 'Settings', 'smart-post-show' ) . '</h1>';
			echo '</div>';

			echo '<div class="spf-header-right">';

			echo ( $has_nav && $this->args['show_all_options'] ) ? '<div class="spf-expand-all" title="' . esc_html__( 'show all options', 'smart-post-show' ) . '"><i class="fa fa-outdent"></i></div>' : '';

			echo ( $this->args['show_search'] ) ? '<div class="spf-search"><input type="text" name="spf-search" placeholder="' . esc_html__( 'Search option(s)', 'smart-post-show' ) . '" autocomplete="off" /></div>' : '';

			echo '<div class="spf-buttons">';
			echo '<input type="submit" name="' . $this->unique . '[_nonce][save]" class="button button-primary spf-save' . $ajax_class . '" value="' . esc_html__( 'Save', 'smart-post-show' ) . '" data-save="' . esc_html__( 'Saving...', 'smart-post-show' ) . '">';
			echo ( $this->args['show_reset_section'] ) ? '<input type="submit" name="spf_transient[reset_section]" class="button button-secondary spf-reset-section spf-confirm" value="' . esc_html__( 'Reset Section', 'smart-post-show' ) . '" data-confirm="' . esc_html__( 'Are you sure to reset this section options?', 'smart-post-show' ) . '">' : '';
			echo ( $this->args['show_reset_all'] ) ? '<input type="submit" name="spf_transient[reset]" class="button spf-reset-all spf-confirm" value="' . esc_html__( 'Reset All', 'smart-post-show' ) . '" data-confirm="' . esc_html__( 'Are you sure to reset all options?', 'smart-post-show' ) . '">' : '';
			echo '</div>';

			echo '</div>';

			echo '<div class="clear"></div>';
			echo '</div>';
			echo '</div>';

			echo '<div class="spf-wrapper' . $show_all . '">';

			if ( $has_nav ) {
				echo '<div class="spf-nav spf-nav-options">';

				echo '<ul>';

				$tab_key = 1;

				foreach ( $this->pre_tabs as $tab ) {

					$tab_error = $this->error_check( $tab );
					$tab_icon  = ( ! empty( $tab['icon'] ) ) ? '<i class="' . $tab['icon'] . '"></i>' : '';

					if ( ! empty( $tab['subs'] ) ) {

						echo '<li class="spf-tab-depth-0">';

						echo '<a href="#tab=' . $tab_key . '" class="spf-arrow">' . $tab_icon . $tab['title'] . $tab_error . '</a>';

						echo '<ul>';

						foreach ( $tab['subs'] as $sub ) {

							$sub_error = $this->error_check( $sub );
							$sub_icon  = ( ! empty( $sub['icon'] ) ) ? '<i class="' . $sub['icon'] . '"></i>' : '';

							echo '<li class="spf-tab-depth-1"><a id="spf-tab-link-' . $tab_key . '" href="#tab=' . $tab_key . '">' . $sub_icon . $sub['title'] . $sub_error . '</a></li>';

							$tab_key++;
						}

						echo '</ul>';

						echo '</li>';

					} else {

						echo '<li class="spf-tab-depth-0"><a id="spf-tab-link-' . $tab_key . '" href="#tab=' . $tab_key . '">' . $tab_icon . $tab['title'] . $tab_error . '</a></li>';

						$tab_key++;
					}
				}

				echo '</ul>';

				echo '</div>';

			}

			echo '<div class="spf-content">';

			echo '<div class="spf-sections">';

			$section_key = 1;

			foreach ( $this->pre_sections as $section ) {

				$onload       = ( ! $has_nav ) ? ' spf-onload' : '';
				$section_icon = ( ! empty( $section['icon'] ) ) ? '<i class="spf-icon ' . $section['icon'] . '"></i>' : '';

				echo '<div id="spf-section-' . $section_key . '" class="spf-section' . $onload . '">';
				echo ( $has_nav ) ? '<div class="spf-section-title"><h3>' . $section_icon . $section['title'] . '</h3></div>' : '';
				echo ( ! empty( $section['description'] ) ) ? '<div class="spf-field spf-section-description">' . $section['description'] . '</div>' : '';

				if ( ! empty( $section['fields'] ) ) {

					foreach ( $section['fields'] as $field ) {

						$is_field_error = $this->error_check( $field );

						if ( ! empty( $is_field_error ) ) {
							$field['_error'] = $is_field_error;
						}

						$value = ( ! empty( $field['id'] ) && isset( $this->options[ $field['id'] ] ) ) ? $this->options[ $field['id'] ] : '';

						SP_PC::field( $field, $value, $this->unique, 'options' );

					}
				} else {

					echo '<div class="spf-no-option spf-text-muted">' . esc_html__( 'No option provided by developer.', 'smart-post-show' ) . '</div>';

				}

				echo '</div>';

				$section_key++;
			}

			echo '</div>';

			echo '<div class="clear"></div>';

			echo '</div>';

			echo '<div class="spf-nav-background"></div>';

			echo '</div>';

			if ( ! empty( $this->args['show_footer'] ) ) {

				echo '<div class="spf-footer">';

				echo '<div class="spf-buttons">';
				echo '<input type="submit" name="spf_transient[save]" class="button button-primary spf-save' . $ajax_class . '" value="' . esc_html__( 'Save', 'smart-post-show' ) . '" data-save="' . esc_html__( 'Saving...', 'smart-post-show' ) . '">';
				echo ( $this->args['show_reset_section'] ) ? '<input type="submit" name="spf_transient[reset_section]" class="button button-secondary spf-reset-section spf-confirm" value="' . esc_html__( 'Reset Section', 'smart-post-show' ) . '" data-confirm="' . esc_html__( 'Are you sure to reset this section options?', 'smart-post-show' ) . '">' : '';
				echo ( $this->args['show_reset_all'] ) ? '<input type="submit" name="spf_transient[reset]" class="button spf-reset-all spf-confirm" value="' . esc_html__( 'Reset All', 'smart-post-show' ) . '" data-confirm="' . esc_html__( 'Are you sure to reset all options?', 'smart-post-show' ) . '">' : '';
				echo '</div>';

				echo ( ! empty( $this->args['footer_text'] ) ) ? '<div class="spf-copyright">' . $this->args['footer_text'] . '</div>' : '';

				echo '<div class="clear"></div>';
				echo '</div>';

			}

			echo '</form>';

			echo '</div>';

			echo '<div class="clear"></div>';

			echo ( ! empty( $this->args['footer_after'] ) ) ? $this->args['footer_after'] : '';

			echo '</div>';

		}
	}
}
