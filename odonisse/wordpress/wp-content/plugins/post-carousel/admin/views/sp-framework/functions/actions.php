<?php if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access directly.

if ( ! function_exists( 'pcp_get_option' ) ) {
	/**
	 * The pcp_get_option function.
	 *
	 * @param string $option The option unique ID.
	 * @param mixed  $default The default value for the option.
	 * @return statement
	 */
	function pcp_get_option( $option = '', $default = null ) {
		$options = get_option( 'sp_pcp_settings' );
		return ( isset( $options[ $option ] ) ) ? $options[ $option ] : $default;
	}
}

/**
 * Populate the taxonomy name list to he select option.
 *
 * @return void
 */
function sps_get_taxonomies() {
	// Check for nonce security.
	check_ajax_referer( 'spf_pcp_metabox_nonce', wp_unslash( $_POST['ajax_nonce'] ), false );
	$the_pcp_post_types = wp_unslash( $_POST['pcp_post_types'] );
	$sp_post_types      = $the_pcp_post_types ? $the_pcp_post_types : get_post_types( [], 'names' );

	$taxonomy_names = get_object_taxonomies( $sp_post_types, 'names' );
	if ( ! is_wp_error( $taxonomy_names ) && ! empty( $taxonomy_names ) ) {
		echo '<option value="">' . __( 'Select Taxonomy', 'smart-post-show' ) . '</option>';
		foreach ( $taxonomy_names as $taxonomy => $label ) {
			echo '<option value="' . $label . '">' . $label . '</option>';
		}
	}
	die( 0 );
}
add_action( 'wp_ajax_sps_get_taxonomies', 'sps_get_taxonomies' );

/**
 * Populate the taxonomy terms list to the select option.
 *
 * @return void
 */
function sps_get_terms() {
	// Check for nonce security.
	check_ajax_referer( 'spf_pcp_metabox_nonce', wp_unslash( $_POST['ajax_nonce'] ), false );
	$the_pcp_taxonomy = wp_unslash( $_POST['pcp_post_taxonomy'] );
	$sp_post_types    = get_post_types( [], 'names' );
	$pcp_taxonomy     = $the_pcp_taxonomy ? $the_pcp_taxonomy : get_object_taxonomies( $sp_post_types, 'names' );
	if ( version_compare( get_bloginfo( 'version' ), '4.5', '>=' ) ) {
		$terms = get_terms( array( 'taxonomy' => $pcp_taxonomy ) );
	} else {
		$terms = get_terms( array( $pcp_taxonomy ) );
	}

	foreach ( $terms as $key => $value ) {
		echo '<option value="' . $value->term_id . '">' . $value->name . '</option>';
	}
	wp_die();
}
add_action( 'wp_ajax_sps_get_terms', 'sps_get_terms' );


if ( ! function_exists( 'sps_get_icons' ) ) {
	/**
	 *
	 * Get icons from admin ajax
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function sps_get_icons() {

		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'spf_icon_nonce' ) ) {

			ob_start();

			SP_PC::include_plugin_file( 'fields/icon/default-icons.php' );

			$icon_lists = apply_filters( 'spf_field_icon_add_icons', spf_get_default_icons() );

			if ( ! empty( $icon_lists ) ) {

				foreach ( $icon_lists as $list ) {

					echo ( count( $icon_lists ) >= 2 ) ? '<div class="spf-icon-title">' . $list['title'] . '</div>' : '';

					foreach ( $list['icons'] as $icon ) {
						echo '<a class="spf-icon-tooltip" data-spf-icon="' . $icon . '" title="' . $icon . '"><span class="spf-icon spf-selector"><i class="' . $icon . '"></i></span></a>';
					}
				}
			} else {
				echo '<div class="spf-text-error">' . esc_html__( 'No data provided by developer', 'smart-post-show' ) . '</div>';
			}

			wp_send_json_success( array( 'content' => ob_get_clean() ) );

		} else {

			wp_send_json_error( array( 'error' => esc_html__( 'Error: Nonce verification has failed. Please try again.', 'smart-post-show' ) ) );

		}

	}
	add_action( 'wp_ajax_spf-get-icons', 'sps_get_icons' );
}

if ( ! function_exists( 'spf_export' ) ) {

	/**
	 *
	 * Export
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function spf_export() {

		if ( ! empty( $_GET['export'] ) && ! empty( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'spf_backup_nonce' ) ) {
			header( 'Content-Type: application/json' );
			header( 'Content-disposition: attachment; filename=backup-' . gmdate( 'd-m-Y' ) . '.json' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			echo json_encode( get_option( wp_unslash( $_GET['export'] ) ) );
		}
		die();
	}
	add_action( 'wp_ajax_spf-export', 'spf_export' );
}

if ( ! function_exists( 'spf_import_ajax' ) ) {
	/**
	 *
	 * Import Ajax
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function spf_import_ajax() {

		if ( ! empty( $_POST['import_data'] ) && ! empty( $_POST['unique'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'spf_backup_nonce' ) ) {
			$import_data = json_decode( wp_unslash( trim( $_POST['import_data'] ) ), true );

			if ( is_array( $import_data ) ) {

				update_option( wp_unslash( $_POST['unique'] ), wp_unslash( $import_data ) );
				wp_send_json_success();

			}
		}

		wp_send_json_error( array( 'error' => esc_html__( 'Error: Nonce verification has failed. Please try again.', 'smart-post-show' ) ) );

	}
	add_action( 'wp_ajax_spf-import', 'spf_import_ajax' );
}

if ( ! function_exists( 'spf_reset_ajax' ) ) {

	/**
	 *
	 * Reset Ajax
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function spf_reset_ajax() {

		if ( ! empty( $_POST['unique'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'spf_backup_nonce' ) ) {
			delete_option( wp_unslash( $_POST['unique'] ) );
			wp_send_json_success();
		}

		wp_send_json_error( array( 'error' => esc_html__( 'Error: Nonce verification has failed. Please try again.', 'smart-post-show' ) ) );

	}
	add_action( 'wp_ajax_spf-reset', 'spf_reset_ajax' );
}

if ( ! function_exists( 'spf_chosen_ajax' ) ) {
	/**
	 *
	 * Chosen Ajax
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function spf_chosen_ajax() {
		if ( ! empty( $_POST['term'] ) && ! empty( $_POST['type'] ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'spf_chosen_ajax_nonce' ) ) {
			$capability = apply_filters( 'spf_chosen_ajax_capability', 'manage_options' );
			if ( current_user_can( $capability ) ) {
				$type       = $_POST['type'];
				$term       = $_POST['term'];
				$query_args = ( ! empty( $_POST['query_args'] ) ) ? $_POST['query_args'] : array();
				$options    = SP_PC_Fields::field_data( $type, $term, $query_args );
				wp_send_json_success( $options );
			} else {
				wp_send_json_error( array( 'error' => esc_html__( 'You do not have required permissions to access.', 'smart-post-show' ) ) );
			}
		} else {
			wp_send_json_error( array( 'error' => esc_html__( 'Error: Nonce verification has failed. Please try again.', 'smart-post-show' ) ) );
		}
	}
	add_action( 'wp_ajax_spf-chosen', 'spf_chosen_ajax' );
}

if ( ! function_exists( 'spf_set_icons' ) ) {
	/**
	 *
	 * Set icons for wp dialog
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	function spf_set_icons() {
		global $post_type;
		if ( 'sp_post_carousel' === $post_type ) {
			?>
			<div id="spf-modal-icon" class="spf-modal spf-modal-icon">
				<div class="spf-modal-table">
				<div class="spf-modal-table-cell">
					<div class="spf-modal-overlay"></div>
					<div class="spf-modal-inner">
					<div class="spf-modal-title">
						<?php esc_html_e( 'Add Icon', 'smart-post-show' ); ?>
						<div class="spf-modal-close spf-icon-close"></div>
					</div>
					<div class="spf-modal-header spf-text-center">
						<input type="text" placeholder="<?php esc_html_e( 'Search a Icon...', 'smart-post-show' ); ?>" class="spf-icon-search" />
					</div>
					<div class="spf-modal-content">
						<div class="spf-modal-loading"><div class="spf-loading"></div></div>
						<div class="spf-modal-load"></div>
					</div>
					</div>
				</div>
				</div>
			</div>
			<?php
		}

	}
	add_action( 'admin_footer', 'spf_set_icons' );
	add_action( 'customize_controls_print_footer_scripts', 'spf_set_icons' );
}