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
	
	add_action( 'admin_init', 'audiotheme_upgrade' );

	add_action( 'update_option_audiotheme_disable_directory_browsing', 'audiotheme_disable_directory_browsing_option_update' );

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
	$license = get_option( 'audiotheme_license_key' );

	// Don't do the remote request if a license key hasn't been entered.
	if ( ! $license ) {
		add_filter( 'do_audiotheme_update_request', '__return_false' );
		add_filter( 'audiotheme_update_plugin_notice-audiotheme', 'audiotheme_update_notice' );
	}

	$api_data = array( 'license' => $license );

	$framework_updater = new Audiotheme_Updater_Plugin( array(
		'api_url'  => 'http://127.0.0.1/woocommerce/api/',
		'api_data' => $api_data
	), AUDIOTHEME_DIR . 'audiotheme.php' );

	$framework_updater->init();

	if ( current_theme_supports( 'audiotheme-automatic-updates' ) ) {
		include( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-theme.php' );

		$support = get_theme_support( 'audiotheme-automatic-updates' );
		$support = wp_parse_args( $support[0], array( 'api_data' => $api_data ) );

		$theme_updater = new Audiotheme_Updater_Theme( $support );
		$theme_updater->init();
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
	$notice  =  sprintf( __( '<a href="%s">Register your copy of AudioTheme</a> to receive automatic updates and support. Need a license key?', 'audiotheme-i18n' ),
		esc_url( add_query_arg( 'page', 'audiotheme-settings', admin_url( 'admin.php' ) ) )
	);
	$notice .= ' <a href="http://audiotheme.com/view/audiotheme/" target="_blank">' . __( 'Purchase one now.', 'audiotheme-i18n' ) . '</a>';

	return $notice;
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

	wp_register_style( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/css/admin.css' );
	wp_register_style( 'jquery-ui-theme-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'admin/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );
}

/**
 * Update directory browsing preference.
 *
 * Whenever the directory browsing setting is updated, update .htaccess
 *
 * @since 1.0.0
 */
function audiotheme_disable_directory_browsing_option_update() {
	audiotheme_save_htaccess();
}

/**
 * Save .htacess file.
 *
 * @see save_mod_rewrite_rules()
 *
 * @since 1.0.0
 * */
function audiotheme_save_htaccess() {
	$home_path = get_home_path();
	$htaccess_file = $home_path . '.htaccess';

	if ( ( ! file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) ) {
		$htaccess_contents = file_get_contents( $htaccess_file );

		$directive = 'Options All -Indexes';
		$rules = array();
		if ( get_option( 'audiotheme_disable_directory_browsing' ) && false === strpos( $htaccess_contents, $directive ) ) {
			$rules[] = $directive;
		}

		insert_with_markers( $htaccess_file, 'AudioTheme', $rules );
	}
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
function audiotheme_admin_body_class( $class ) {
	global $post;
	
	$class .= ' ' . sanitize_html_class( get_current_screen()->id );
	
	if ( 'audiotheme_archive' == get_current_screen()->id && $post_type = is_audiotheme_post_type_archive_id( $post->ID )) {
		$class .= ' ' . $post_type . '-archive';
	}
	
	return $class;
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
	$contactmethods['twitter'] = __( 'Twitter Username', 'audiotheme-i18n' );
	$contactmethods['facebook'] = __( 'Facebook URL', 'audiotheme-i18n' );

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
