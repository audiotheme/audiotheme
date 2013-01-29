<?php
/**
 * Admin Includes
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
 * Admin Setup
 *
 * @since 1.0.0
 */
add_action( 'after_setup_theme', 'audiotheme_admin_setup' );

function audiotheme_admin_setup() {
	add_action( 'init', 'audiotheme_admin_init' );
	add_action( 'init', 'audiotheme_automatic_updates' );
	add_action( 'init', 'audiotheme_settings_init' );
	add_action( 'init', 'audiotheme_dashboard_init', 9 );
	add_action( 'init', 'audiotheme_archive_pages_init' );

	add_action( 'update_option_audiotheme_disable_directory_browsing', 'audiotheme_disable_directory_browsing_option_update', 10, 2 );

	add_action( 'admin_enqueue_scripts', 'audiotheme_enqueue_admin_scripts' );
	add_action( 'admin_body_class', 'audiotheme_admin_body_class' );
	add_filter( 'user_contactmethods', 'audiotheme_edit_user_contact_info' );

	add_action( 'manage_pages_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	add_action( 'manage_posts_custom_column', 'audiotheme_display_custom_column', 10, 2 );

	// Print javascript pointer object.
	add_action( 'admin_print_footer_scripts', 'audiotheme_print_pointers' );
}

/**
 *
 */
function audiotheme_automatic_updates() {
	// @todo Grab the license key option value or don't do an update.
	$license = '';

	if ( ! $license ) {
		return;
	}

	$api_data = array( 'license' => $license );

	$framework_updater = new Audiotheme_Updater_Plugin( array( 'api_data' => $api_data  ), AUDIOTHEME_DIR . 'audiotheme.php' );
	$framework_updater->init();

	// Automatic theme updates require support for 'audiotheme-theme-options' to be enabled.
	if ( current_theme_supports( 'audiotheme-automatic-updates' ) ) {
		include( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-theme.php' );


		$support = get_theme_support( 'audiotheme-automatic-updates' );
		$support = wp_parse_args( $support[0], array( array( 'api_data' => $api_data ) ) );

		$theme_updater = new Audiotheme_Updater_Theme( $support );
		$theme_updater->init();
	}
}

/**
 *
 */
function audiotheme_admin_init() {
	wp_register_script( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/js/audiotheme-admin.js', array( 'jquery-ui-sortable' ) );
	wp_register_script( 'audiotheme-media', AUDIOTHEME_URI . 'admin/js/audiotheme-media.js', array( 'jquery' ) );
	wp_register_script( 'audiotheme-pointer', AUDIOTHEME_URI . 'admin/js/audiotheme-pointer.js', array( 'wp-pointer' ) );
	wp_register_script( 'audiotheme-settings', AUDIOTHEME_URI . 'admin/js/audiotheme-settings.js' );

	wp_register_style( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/css/audiotheme-admin.css' );
	wp_register_style( 'jquery-ui-theme-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'admin/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );
}

/**
 * Update Directory Browsing
 *
 * Whenever the directory browsing setting is updated, update .htaccess
 *
 * @since 1.0.0
 */
function audiotheme_disable_directory_browsing_option_update( $oldvalue, $newvalue ) {
	audiotheme_save_htaccess();
}

/**
 * Save .htacess
 *
 * Updates the .htaccess file.
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

		return insert_with_markers( $htaccess_file, 'AudioTheme', $rules );
	}
}

/**
 * Enqueue Admin Scripts
 *
 * Should be loaded on every admin request
 *
 * @since 1.0.0
 */
function audiotheme_enqueue_admin_scripts() {
	wp_enqueue_script( 'audiotheme-admin' );
	wp_enqueue_style( 'audiotheme-admin' );
}

/**
 * Add Current Screen ID as CSS Class to <body>
 *
 * @since 1.0.0
 */
function audiotheme_admin_body_class( $class ) {
	return ' ' . sanitize_html_class( get_current_screen()->id );
}

/**
 * Custom Post Type Columns
 *
 * This hook is run for all custom columns, so the column name is prefixed to
 * prevent potential conflicts.
 *
 * @since 1.0.0
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
 * Custom User Contact Fields
 *
 * @since 1.0.0
 * @todo This may conflict with the WordPress SEO plugin.
 */
function audiotheme_edit_user_contact_info( $contactmethods ) {
	// Add contact options
	$contactmethods['twitter'] = __( 'Twitter <span class="description">(username)</span>', 'audiotheme-i18n' );
	$contactmethods['facebook'] = __( 'Facebook  <span class="description">(link)</span>', 'audiotheme-i18n' );

	return $contactmethods;
}
