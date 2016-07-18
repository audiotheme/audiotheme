<?php
/**
 * Settings screen functionality.
 *
 * @package   AudioTheme\Settings
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Settings screen class.
 *
 * @package AudioTheme\Settings
 * @since   2.0.0
 */
class AudioTheme_Screen_Settings extends AudioTheme_Screen_AbstractScreen {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'admin_init', array( $this, 'add_sections' ) );
	}

	/**
	 * Add the settings menu item.
	 *
	 * @since 2.0.0
	 */
	public function add_menu_item() {
		add_submenu_page(
			'audiotheme',
			__( 'Settings', 'audiotheme' ),
			__( 'Settings', 'audiotheme' ),
			'manage_options',
			'audiotheme-settings',
			array( $this, 'display_screen' )
		);
	}

	/**
	 * Add settings sections.
	 *
	 * @since 2.0.0
	 */
	public function add_sections() {
		add_settings_section(
			'default',
			'',
			'__return_null',
			'audiotheme-settings'
		);
	}

	/**
	 * Display the screen.
	 *
	 * @since 2.0.0
	 */
	public function display_screen() {
		include( $this->plugin->get_path( 'admin/views/screen-settings.php' ) );
	}
}
