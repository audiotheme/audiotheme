<?php
/**
 * @todo Add a routine for sorting submenus by priority?
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

	add_action( 'update_option_audiotheme_license', 'audiotheme_license_option_update', 10, 2 );
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
		'option_name'  => array( 'audiotheme_options', 'audiotheme_license', 'audiotheme_disable_directory_browsing' ),
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

		$section->add_field( 'audiotheme_license', __( 'License Key', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_license',
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

function audiotheme_menu_insert_item( $item, $relative_slug, $position = 'after' ) {
	global $menu;

	$relative_key = audiotheme_menu_get_item_key( $relative_slug );
	$before = ( 'before' == $position ) ? $relative_key : $relative_key + 1;

	array_splice( $menu, $before, 0, array( $item ) );
}

function audiotheme_menu_move_item( $move_slug, $relative_slug, $position = 'after' ) {
	global $menu;

	$move_key = audiotheme_menu_get_item_key( $move_slug );
	$item = $menu[ $move_key ];
	unset( $menu[ $move_key ] );

	audiotheme_menu_insert_item( $item, $relative_slug, $position );
}

function audiotheme_menu_get_item_key( $menu_slug ) {
	global $menu;

	foreach ( $menu as $key => $item ) {
		if ( $menu_slug == $item[2] ) {
			return $key;
		}
	}

	return false;
}

function audiotheme_submenu_move_after( $move_slug, $after_slug, $menu_slug ) {
	global $submenu;

	if ( isset( $submenu[ $menu_slug ] ) ) {
		foreach ( $submenu[ $menu_slug ] as $key => $item ) {
			if ( $item[2] == $move_slug ) {
				$move_key = $key;
			} elseif ( $item[2] == $after_slug ) {
				$after_key = $key;
			}
		}

		if ( isset( $move_key ) && isset( $after_key ) ) {
			$move_item = $submenu[ $menu_slug ][ $move_key ];
			unset( $submenu[ $menu_slug ][ $move_key ] );

			// Need to account for the change in the the array with the previous unset.
			$new_position = ( $move_key > $after_key ) ? $after_key + 1 : $after_key;

			array_splice( $submenu[ $menu_slug ], $new_position, 0, array( $move_item ) );
		}
	}
}
