<?php
/**
 * AudioTheme framework administration bootstrap.
 *
 * @package AudioTheme\Administration
 */

/**
 * Admin includes.
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'admin/functions.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen-dashboard.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen-network-settings.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen-settings.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-setting-licensekey.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-plugin.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-theme.php' );
require( AUDIOTHEME_DIR . 'admin/deprecated/class-audiotheme-settings.php' );
require( AUDIOTHEME_DIR . 'admin/deprecated/dashboard.php' );
require( AUDIOTHEME_DIR . 'admin/deprecated/deprecated.php' );
require( AUDIOTHEME_DIR . 'admin/deprecated/settings-screens.php' );

/**
 * Set up the admin.
 *
 * @since 1.0.0
 */
function audiotheme_admin_setup() {
	add_action( 'init', 'audiotheme_admin_init' );
	add_action( 'init', 'audiotheme_update' );
	add_action( 'init', 'audiotheme_settings_init' );
	add_action( 'init', 'audiotheme_dashboard_init', 9 );

	add_action( 'http_request_args', 'audiotheme_update_request', 10, 2 );
	add_action( 'admin_init', 'audiotheme_upgrade' );
	add_action( 'admin_init', 'audiotheme_dashboard_sort_menu' );

	add_action( 'admin_enqueue_scripts', 'audiotheme_enqueue_admin_scripts' );
	add_action( 'admin_body_class', 'audiotheme_admin_body_class' );
	add_action( 'save_post', 'audiotheme_update_post_terms', 10, 2 );

	add_action( 'manage_pages_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	add_action( 'manage_posts_custom_column', 'audiotheme_display_custom_column', 10, 2 );
}

/**
 * Set up the framework dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_init() {
	$plugin = audiotheme();

	$plugin->register_hooks( new AudioTheme_Screen_Dashboard() );
	$plugin->register_hooks( new AudioTheme_Screen_Settings() );

	if ( is_network_admin() ) {
		$plugin->register_hooks( new AudioTheme_Screen_Network_Settings() );
	}

	$license = audiotheme()->license;
	$plugin->register_hooks( new AudioTheme_Setting_LicenseKey( $license ) );
}

/**
 * Check for AudioTheme framework and theme updates.
 *
 * @since 1.0.0
 */
function audiotheme_update() {
	if ( is_multisite() && ! is_network_admin() ) {
		return;
	}

	$license = audiotheme()->license;

	// Don't do the remote request if a license key hasn't been entered.
	if ( ! $license->has_key() ) {
		add_filter( 'do_audiotheme_update_request', '__return_false' );
		add_filter( 'audiotheme_update_plugin_notice-audiotheme', 'audiotheme_update_notice' );
	}

	$api_data = array( 'license' => $license->get_key() );

	$framework_updater = new Audiotheme_Updater_Plugin( array(
		'api_data' => $api_data,
	), AUDIOTHEME_DIR . 'audiotheme.php' );

	$framework_updater->init();

	if ( current_theme_supports( 'audiotheme-automatic-updates' ) ) {
		$support = get_theme_support( 'audiotheme-automatic-updates' );
		$args = wp_parse_args( $support[0], array( 'api_data' => $api_data ) );

		$theme_updater = new Audiotheme_Updater_Theme( $args );
		$theme_updater->init();

		// Add the theme to a list to check for updates in multisite.
		audiotheme_update_themes_list( get_template(), $support[0] );
	}

	// Check for updates to all AudioTheme themes in the network admin.
	if ( is_network_admin() && ( $themes = get_site_option( 'audiotheme_themes' ) ) ) {
		// Filter out invalid theme slugs.
		$check = array_intersect_key( $themes, wp_get_themes() );

		if ( $check ) {
			foreach ( $check as $slug => $args ) {
				$args = wp_parse_args( $args, array( 'api_data' => $api_data ) );

				$theme_updater = new Audiotheme_Updater_Theme( $args );
				$theme_updater->init();
			}
		}

		if ( count( $check ) !== count( $themes ) ) {
			update_site_option( 'audiotheme_themes', $check );
		}
	}
}

/**
 * Display a notice to register if the license key is empty.
 *
 * @since 1.0.0
 *
 * @param string $notice The default notice.
 * @return string
 */
function audiotheme_update_notice( $notice ) {
	$settings_page = is_network_admin() ? 'network/settings.php' : 'admin.php';

	$notice  = sprintf( __( '<a href="%s">Register your copy of AudioTheme</a> to receive automatic updates and support. Need a license key?', 'audiotheme' ),
		esc_url( add_query_arg( 'page', 'audiotheme-settings', admin_url( $settings_page ) ) )
	);
	$notice .= ' <a href="https://audiotheme.com/view/audiotheme/" target="_blank">' . __( 'Purchase one now.', 'audiotheme' ) . '</a>';

	return $notice;
}

/**
 * Disable SSL verification when interacting with audiotheme.com.
 *
 * Prevents automatic updates from failing when 'sslverify' is true.
 *
 * @since 1.0.0
 *
 * @param array  $r Request args.
 * @param string $url URI resource.
 * @return array Filtered request args.
 */
function audiotheme_update_request( $r, $url ) {
	if ( false === strpos( $url, 'audiotheme.com' ) ) {
		return $r; // Not a request to audiotheme.com.
	}

	$r['sslverify'] = false;

	return $r;
}

/**
 * Sort the admin menu.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_sort_menu() {
	global $menu;

	if ( is_network_admin() || ! $menu ) {
		return;
	}

	$menu = array_values( $menu ); // Re-key the array.

	audiotheme_menu_move_item( 'audiotheme', 'separator1', 'before' );

	$separator = array( '', 'read', 'separator-before-audiotheme', '', 'wp-menu-separator' );
	audiotheme_menu_insert_item( $separator, 'audiotheme', 'before' );

	// Reverse the order and always insert them after the main AudioTheme menu item.
	audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_video', 'audiotheme' );
	audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_record', 'audiotheme' );
	audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_gig', 'audiotheme' );

	audiotheme_submenu_move_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
}

/**
 * Register scripts and styles for enqueuing when needed.
 *
 * @since 1.0.0
 */
function audiotheme_admin_init() {
	$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$license = audiotheme()->license;

	wp_register_script( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/js/admin.bundle' . $suffix . '.js', array( 'jquery-ui-sortable', 'underscore', 'wp-util' ), AUDIOTHEME_VERSION, true );
	wp_register_script( 'audiotheme-dashboard', AUDIOTHEME_URI . 'admin/js/dashboard.js',array( 'jquery', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );
	wp_register_script( 'audiotheme-license', AUDIOTHEME_URI . 'admin/js/license.js', array( 'jquery', 'wp-util' ), AUDIOTHEME_VERSION, true );
	wp_register_script( 'audiotheme-media', AUDIOTHEME_URI . 'admin/js/media' . $suffix . '.js', array( 'jquery' ), AUDIOTHEME_VERSION, true );
	wp_register_script( 'audiotheme-settings', AUDIOTHEME_URI . 'admin/js/settings' . $suffix . '.js', array(), AUDIOTHEME_VERSION, true );

	wp_register_style( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/css/admin.min.css' );
	wp_register_style( 'audiotheme-dashboard', AUDIOTHEME_URI. 'admin/css/dashboard.min.css' );
	wp_register_style( 'jquery-ui-theme-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'admin/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );

	wp_localize_script( 'audiotheme-admin', '_audiothemeAdminSettings', array(
		'licenseKey'    => $license->get_key(),
		'licenseStatus' => $license->is_valid() ? 'active' : 'inactive',
	) );

	wp_localize_script( 'audiotheme-dashboard', '_audiothemeDashboardSettings', array(
		'canActivateModules' => current_user_can( 'activate_plugins' ),
		'modules'            => audiotheme()->modules->prepare_for_js(),
		'l10n'               => array(
			'activate'   => __( 'Activate', 'audiotheme' ),
			'deactivate' => __( 'Deactivate', 'audiotheme' ),
		),
	) );

	wp_localize_script( 'audiotheme-license', '_audiothemeLicenseSettings', array(
		'activatedResponse' => sprintf( ' <strong class="audiotheme-response is-valid">%s</strong>', __( 'Activated!', 'audiotheme' ) ),
		'nonce'             => wp_create_nonce( 'audiotheme-activate-license' ),
		// Statuses: ok|empty|unknown|invalid|expired|limit_reached|failed.
		'errorMessages'     => array(
			'empty'         => __( 'Empty license key.', 'audiotheme' ),
			'invalid'       => __( 'Invalid license key.', 'audiotheme' ),
			'expired'       => __( 'License key expired.', 'audiotheme' ) .' <a href="https://audiotheme.com/view/audiotheme/" target="_blank">' . __( 'Renew now.', 'audiotheme' ) . '</a>',
			'limit_reached' => __( 'Activation limit reached.', 'audiotheme' ) . ' <a href="https://audiotheme.com/view/audiotheme/" target="_blank">' . __( 'Upgrade your license.', 'audiotheme' ) . '</a>',
			'generic'       => __( 'An unknown error occurred while checking the licensing server.', 'audiotheme' ),
		),
	) );

	wp_localize_script( 'audiotheme-media', 'AudiothemeMediaControl', array(
		'audioFiles'      => __( 'Audio files', 'audiotheme' ),
		'frameTitle'      => __( 'Choose an Attachment', 'audiotheme' ),
		'frameUpdateText' => __( 'Update Attachment', 'audiotheme' ),
	) );
}

/**
 * Enqueue admin scripts and styles.
 *
 * @since 1.0.0
 */
function audiotheme_enqueue_admin_scripts() {
	wp_enqueue_script( 'audiotheme-admin' );
	wp_enqueue_style( 'audiotheme-admin' );
}

/**
 * Add current screen ID as CSS class to the body element.
 *
 * @since 1.0.0
 *
 * @param string $classes Body classes.
 * @return string
 */
function audiotheme_admin_body_class( $classes ) {
	global $post;

	$classes .= ' screen-' . sanitize_html_class( get_current_screen()->id );

	if ( 'audiotheme_archive' === get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID ) ) {
		$classes .= ' ' . $post_type . '-archive';
	}

	return implode( ' ', array_unique( explode( ' ', $classes ) ) );
}

/**
 * General custom post type columns.
 *
 * This hook is run for all custom columns, so the column name is prefixed to
 * prevent potential conflicts.
 *
 * @since 1.0.0
 *
 * @param string $column_name Column identifier.
 * @param int    $post_id Post ID.
 */
function audiotheme_display_custom_column( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'audiotheme_image' :
			printf( '<a href="%1$s">%2$s</a>',
				esc_url( get_edit_post_link( $post_id ) ),
				get_the_post_thumbnail( $post_id, array( 60, 60 ) )
			);
			break;
	}
}

/**
 * Save custom taxonomy terms when a post is saved.
 *
 * @since 1.7.0
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post Post object.
 */
function audiotheme_update_post_terms( $post_id, $post ) {
	$is_autosave = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$is_revision = wp_is_post_revision( $post_id );

	// Bail if the data shouldn't be saved.
	if ( $is_autosave || $is_revision || empty( $_POST['audiotheme_post_terms'] ) ) {
		return;
	}

	foreach ( $_POST['audiotheme_post_terms'] as $taxonomy => $term_ids ) {
		// Don't save if intention can't be verified.
		if ( ! isset( $_POST[ $taxonomy . '_nonce' ] ) || ! wp_verify_nonce( $_POST[ $taxonomy . '_nonce' ], 'save-post-terms_' . $post_id ) ) {
			continue;
		}

		$term_ids = array_map( 'absint', $term_ids );
		wp_set_object_terms( $post_id, $term_ids, $taxonomy );
	}
}

/**
 * Upgrade routine.
 *
 * @since 1.0.0
 */
function audiotheme_upgrade() {
	$saved_version   = get_option( 'audiotheme_version', '0' );
	$current_version = AUDIOTHEME_VERSION;

	if ( version_compare( $saved_version, '1.7.0', '<' ) ) {
		audiotheme_upgrade_170();
	}

	if ( version_compare( $saved_version, '1.9.0', '<' ) ) {
		audiotheme_upgrade_190();
	}

	if ( '0' === $saved_version || version_compare( $saved_version, $current_version, '<' ) ) {
		update_option( 'audiotheme_version', AUDIOTHEME_VERSION );
	}
}

/**
 * Upgrade routine for version 1.7.0.
 *
 * @since 1.7.0
 */
function audiotheme_upgrade_170() {
	// Update record types.
	$terms = get_terms( 'audiotheme_record_type', array( 'get' => 'all' ) );
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$name = get_audiotheme_record_type_string( $term->slug );
			$name = empty( $name ) ? ucwords( str_replace( array( 'record-type-', '-' ), array( '', ' ' ), $term->name ) ) : $name;
			$slug = str_replace( 'record-type-', '', $term->slug );

			$result = wp_update_term( $term->term_id, 'audiotheme_record_type', array(
				'name' => $name,
				'slug' => $slug,
			) );

			if ( is_wp_error( $result ) ) {
				// Update the name only. We'll account for the 'record-type-' prefix.
				wp_update_term( $term->term_id, 'audiotheme_record_type', array(
					'name' => $name,
				) );
			}
		}
	}
}

/**
 * Upgrade routine for version 1.9.0.
 *
 * @since 1.9.0
 */
function audiotheme_upgrade_190() {
	// Add the archive post type to its metadata.
	if ( $archives = get_option( 'audiotheme_archives_inactive' ) ) {
		foreach ( $archives as $post_type => $post_id ) {
			update_post_meta( $post_id, 'archive_for_post_type', $post_type );
		}

		// Empty the option, but keep it around to prevent an extra SQL query.
		update_option( 'audiotheme_archives_inactive', array() );
	}

	// Add the archive post type to its metadata.
	if ( $archives = get_option( 'audiotheme_archives' ) ) {
		foreach ( $archives as $post_type => $post_id ) {
			update_post_meta( $post_id, 'archive_for_post_type', $post_type );
		}
	}
}
