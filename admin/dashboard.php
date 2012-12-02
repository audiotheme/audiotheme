<?php
/**
 * @todo Add a routine for sorting submenus by priority?
 */

function audiotheme_dashboard_init() {
	add_action( 'audiotheme_register_settings', 'audiotheme_dashboard_register_settings' );
	add_action( 'admin_menu', 'audiotheme_dashboard_admin_menu' );
	add_action( 'admin_init', 'audiotheme_dashboard_sort_menu' );
}

function audiotheme_dashboard_register_settings() {
	$screen = add_audiotheme_settings_screen( 'audiotheme-settings', __( 'Settings', 'audiotheme-i18n' ), array(
		'menu_title'    => __( 'Settings', 'audiotheme-i18n' ),
		'option_group'  => 'audiotheme_options',
		'option_name'   => array( 'audiotheme_options', 'audiotheme_licenses' ),
		'show_in_menu'  => 'audiotheme',
		'capability'    => 'manage_options'
	) );
	
	$screen->add_field( 'settings_info', __( 'Info' ), 'html', array(
		'output' => 'Move the privacy option over here?<br>
			Features should have fallbacks, CSS and JS may be able to be disabled here if the theme excplicitly removes support.'
	) );
	
	$screen->add_field( 'permalinks', __( 'Permalinks' ), 'html', array(
		'output' => 'You can customize the URLs for your gigs and discography on the <a href="' . admin_url( 'options-permalink.php' ) . '">Permalinks settings screen</a>.'
	) );
	
	
	// License Tab
	$tab = $screen->add_tab( 'license', __( 'Licenses', 'audiotheme-i18n' ) );
		
		$section = $tab->add_section( 'framework_license', '', array(
			'priority' => 0,
			'callback' => 'audiotheme_dashboard_settings_license_section'
		) );
		
		$section->add_field( 'framework_license', __( 'Framework License Key', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'priority'    => 0,
			'option_name' => 'audiotheme_licenses',
			'description' => '<br>I wonder if the Settings API is too plain for something like this? Need to add some help somewheres. Help tab? Link to AudioTheme.com?'
		) );
		
		
		// @todo Only register this section if the theme supports automatic updates.
		$section = $tab->add_section( 'theme_license', __( 'Current Theme', 'audiotheme-i18n' ), array(
			'priority' => 5,
			// 'callback to add description'
		) );
		
		// @todo The field id should incorporate the theme name.
		$section->add_field( 'theme_license', __( 'Theme License Key', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'priority'    => 0,
			'option_name' => 'audiotheme_licenses',
			'description' => '<br>This is the key for the currently active theme if it supports automatic updates.'
		) );
		
		
		// @todo Add a method for add-ons to easily register automatic update support.
		$section = $tab->add_section( 'addon_licenses', __( 'Add-on Licenses', 'audiotheme-i18n' ), array(
			'priority' => 10,
			// 'callback to add description'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license', __( 'Mobile Dashboard', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses',
			'description' => '<br>This would be a license key for an add-on.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license2', __( 'Venue Database Sync', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license3', __( 'Tunebox', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license4', __( 'Newsletter Manager', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license5', __( 'Bandcamp Integration', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license6', __( 'SoundCloud Integration', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license7', __( 'Instagram Integration', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license8', __( 'Gigs Pro', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on.<br>A repeater for quickly adding multiple gigs and a calendar view.<br>Add a setlist to each gig.'
		) );
		
		// @todo The field id should incorporate the add-on name.
		$section->add_field( 'addon_license9', __( 'Multi-Artist Add-on for Labels', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_licenses', 
			'description' => '<br>This would be a license key for an add-on. It\'s using a custom callback to render a field.'
		) );
	
	// Status Tab
	$tab = $screen->add_tab( 'info', __( 'Status', 'audiotheme-i18n' ) );
	$tab->add_field( 'data', __( 'Installation Status', 'audiotheme-i18n' ), 'html', array(
		'output' => 'Output the AudioTheme version, MySQL version, WordPress version, etc. for support. Maybe a field to dump a bunch of debug data for copying and pasting.'
	) );
}

function audiotheme_dashboard_settings_license_section( $section ) {
	echo '';
}

/**
 * @todo Custom field callback for license key fields to activate and validate the keys without doing a post back. Should add elegant error reporting.
 * @todo Pass an arg that lets this routine determine whether or not a button should be output or a valid status message.
 */
function audiotheme_dashboard_license_input( $args ) {
	extract( $args );
	
	$value = get_audiotheme_option( $option_name, $key, $default  );
	
	printf( '<input type="text" name="%s" id="%s" value="%s" class="audiotheme-settings-license-text audiotheme-settings-text regular-text">',
		esc_attr( $field_name ),
		esc_attr( $field_id ),
		esc_attr( $value )
	);
	
	echo '<input type="button" value="Check" class="audiotheme-settings-license-button button button-primary"> Good!';
	
	echo '<br>Expires on: 12/31/2012. <a href="">Renew now?</a>';
	$settings = get_audiotheme_settings();
	echo $settings->get_field_description( $args );
}

function audiotheme_dashboard_admin_menu() {
	$pagehook = add_menu_page(
		__( 'AudioTheme', 'audiotheme-i18n' ),
		__( 'AudioTheme', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme',
		'audiotheme_dashboard_features_screen',
		null,
		3 // @todo Make sure this doesn't conflict.
	);
	
	add_submenu_page(
		'audiotheme',
		__( 'Features', 'audiotheme-i18n' ),
		__( 'Features', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme',
		'audiotheme_dashboard_features_screen'
	);
	
	// Hack to get it to show in the correct position for now.
	/*add_submenu_page(
		'audiotheme',
		__( 'AudioTheme Settings', 'audiotheme-i18n' ),
		__( 'Settings', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme-settings',
		'audiotheme_settings_display_screen'
	);*/
	
	add_submenu_page(
		'audiotheme',
		__( 'Add-ons', 'audiotheme-i18n' ),
		__( 'Add-ons', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme-addons',
		'audiotheme_dashboard_addons_screen'
	);
	
	add_submenu_page(
		'audiotheme',
		__( 'Help', 'audiotheme-i18n' ),
		__( 'Help', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme-help',
		'audiotheme_dashboard_help_screen'
	);
}

function audiotheme_dashboard_features_screen() {
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-features.php' );
}

function audiotheme_dashboard_addons_screen() {
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-addons.php' );
}

function audiotheme_dashboard_help_screen() {
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-help.php' );
}




function audiotheme_dashboard_sort_menu() {
	audiotheme_submenu_move_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
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
?>