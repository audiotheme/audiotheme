<?php
/**
 * AudioTheme framework administration bootstrap.
 *
 * @package AudioTheme_Framework
 * @subpackage Administration
 */

/**
 * Admin includes.
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'admin/dashboard.php' );
require( AUDIOTHEME_DIR . 'admin/functions.php' );
require( AUDIOTHEME_DIR . 'admin/includes/archives.php' );
require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-settings.php' );
include( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater.php' );
include( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-plugin.php' );
include( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-theme.php' );
require( AUDIOTHEME_DIR . 'admin/includes/settings-screens.php' );

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
	add_action( 'init', 'audiotheme_archives_init_admin', 50 );

	add_action( 'extra_theme_headers', 'audiotheme_theme_headers' );
	add_action( 'http_request_args', 'audiotheme_update_request', 10, 2 );
	add_action( 'admin_init', 'audiotheme_upgrade' );

	add_action( 'admin_enqueue_scripts', 'audiotheme_enqueue_admin_scripts' );
	add_action( 'admin_body_class', 'audiotheme_admin_body_class' );
	add_filter( 'user_contactmethods', 'audiotheme_edit_user_contact_info' );

	add_action( 'manage_pages_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	add_action( 'manage_posts_custom_column', 'audiotheme_display_custom_column', 10, 2 );

	// Print javascript pointer object.
	add_action( 'admin_print_footer_scripts', 'audiotheme_print_pointers' );
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

	$license = get_option( 'audiotheme_license_key' );

	// Don't do the remote request if a license key hasn't been entered.
	if ( ! $license ) {
		add_filter( 'do_audiotheme_update_request', '__return_false' );
		add_filter( 'audiotheme_update_plugin_notice-audiotheme', 'audiotheme_update_notice' );
	}

	$api_data = array( 'license' => $license );

	$framework_updater = new Audiotheme_Updater_Plugin( array(
		'api_data' => $api_data
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

		if ( count( $check ) != count( $themes ) ) {
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

	$notice  =  sprintf( __( '<a href="%s">Register your copy of AudioTheme</a> to receive automatic updates and support. Need a license key?', 'audiotheme' ),
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
 * @param array $r Request args.
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
 * Add a Template Version header for child themes to declare which version of a
 * parent theme they're compatible with.
 *
 * @since 1.5.0
 *
 * @param array $headers List of extra headers.
 * @return array
 */
function audiotheme_theme_headers( $headers ) {
	$headers['TemplateVersion'] = 'Template Version';
	return $headers;
}

/**
 * Register scripts and styles for enqueuing when needed.
 *
 * @since 1.0.0
 */
function audiotheme_admin_init() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/js/admin' . $suffix . '.js', array( 'underscore', 'jquery-ui-sortable' ) );
	wp_register_script( 'audiotheme-media', AUDIOTHEME_URI . 'admin/js/media' . $suffix . '.js', array( 'jquery' ) );
	wp_register_script( 'audiotheme-pointer', AUDIOTHEME_URI . 'admin/js/pointer' . $suffix . '.js', array( 'wp-pointer' ) );
	wp_register_script( 'audiotheme-settings', AUDIOTHEME_URI . 'admin/js/settings' . $suffix . '.js' );

	$admin_styles  = AUDIOTHEME_URI . 'admin/css/';
	$admin_styles .= version_compare( $GLOBALS['wp_version'], '3.8-alpha', '>' ) ? 'admin.min.css' : 'admin-legacy.min.css';
	wp_register_style( 'audiotheme-admin', $admin_styles );
	wp_register_style( 'jquery-ui-theme-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'admin/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );

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
 * @param string $class Body class.
 * @return string
 */
function audiotheme_admin_body_class( $classes ) {
	global $post;

	$classes .= ' screen-' . sanitize_html_class( get_current_screen()->id );

	if ( 'audiotheme_archive' == get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID )) {
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
 * @param int $post_id Post ID.
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
 * Custom user contact fields.
 *
 * @since 1.0.0
 *
 * @param array $contactmethods List of contact methods.
 * @return array
 */
function audiotheme_edit_user_contact_info( $contactmethods ) {
	$contactmethods['twitter'] = __( 'Twitter Username', 'audiotheme' );
	$contactmethods['facebook'] = __( 'Facebook URL', 'audiotheme' );

	return $contactmethods;
}

/**
 * Upgrade routine.
 *
 * @since 1.0.0
 */
function audiotheme_upgrade() {
	$saved_version = get_option( 'audiotheme_version' );

	if ( ! $saved_version || audiotheme_version_compare( $saved_version, '1.0.0', '<' ) ) {
		update_option( 'audiotheme_version', AUDIOTHEME_VERSION );
	}
}
