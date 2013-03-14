<?php
/**
 * 
 */

/**
 * Setup the framework dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_init() {
	add_action( 'audiotheme_register_settings', 'audiotheme_dashboard_register_settings' );
	add_action( 'admin_menu', 'audiotheme_dashboard_admin_menu' );
	add_action( 'admin_init', 'audiotheme_dashboard_sort_menu' );

	add_action( 'wp_ajax_audiotheme_ajax_activate_license', 'audiotheme_ajax_activate_license' );

	add_action( 'audiotheme_update_response_error', 'audiotheme_license_clear_status' );
	add_action( 'update_option_audiotheme_license_key', 'audiotheme_license_key_option_update', 10, 2 );
}

/**
 * Register default global settings.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_register_settings() {
	$screen = add_audiotheme_settings_screen( 'audiotheme-settings', __( 'Settings', 'audiotheme-i18n' ), array(
		'menu_title'   => __( 'Settings', 'audiotheme-i18n' ),
		'option_group' => 'audiotheme_options',
		'option_name'  => array( 'audiotheme_options', 'audiotheme_license_key', 'audiotheme_disable_directory_browsing' ),
		'show_in_menu' => 'audiotheme',
		'capability'   => 'manage_options'
	) );

	$section = $screen->add_section( 'directory_browsing', __( 'Directory Browsing' ), array(
		'priority' => 50,
		#'callback' => 'audiotheme_dashboard_settings_archive_pages_section',
	) );

		$section->add_field( 'audiotheme_disable_directory_browsing', __( 'Directory Browsing' ), 'checkbox', array(
			'option_name' => 'audiotheme_disable_directory_browsing',
			'choices'     => array(
				'1' => 'Disable directory browsing?',
			),
		) );

	$section = $screen->add_section( 'license', 'License', array(
		'priority' => 0,
		'callback' => 'audiotheme_dashboard_settings_license_section'
	) );

		$section->add_field( 'audiotheme_license_key', __( 'License Key', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_license_key',
		) );


	// System Tab
	$tab = $screen->add_tab( 'info', __( 'System', 'audiotheme-i18n' ) );

		$tab->add_field( 'data', __( 'Installation Status', 'audiotheme-i18n' ), 'html', array(
			'output' => 'Output the AudioTheme version, MySQL version, WordPress version, etc. for support. Maybe a field to dump a bunch of debug data for copying and pasting.'
		) );
}

/**
 * Build the framework admin menu.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_admin_menu() {
	$pagehook = add_menu_page(
		__( 'AudioTheme', 'audiotheme-i18n' ),
		__( 'AudioTheme', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme',
		'audiotheme_dashboard_features_screen',
		null,
		3.901
	);

	add_submenu_page(
		'audiotheme',
		__( 'Features', 'audiotheme-i18n' ),
		__( 'Features', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme',
		'audiotheme_dashboard_features_screen'
	);

	/*
	add_submenu_page(
		'audiotheme',
		__( 'Help', 'audiotheme-i18n' ),
		__( 'Help', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme-help',
		'audiotheme_dashboard_help_screen'
	);
	*/
}

/**
 * Display the main dashboard screen.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_features_screen() {
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-features.php' );
}

/**
 * Display the dashboard help screen.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_help_screen() {
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-help.php' );
}

/**
 * Sort the admin menu.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_sort_menu() {
	global $menu;

	if ( $menu ) {
		$menu = array_values( $menu ); // Re-key the array.

		$separator = array( '', 'read', 'separator-before-audiotheme', '', 'wp-menu-separator' );
		audiotheme_menu_insert_item( $separator, 'audiotheme', 'before' );

		audiotheme_menu_move_item( 'audiotheme-gigs', 'audiotheme' );
		audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_record', 'audiotheme-gigs' );
		audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_video', 'edit.php?post_type=audiotheme_record' );

		audiotheme_submenu_move_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
	}
}
