<?php
/**
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: AudioTheme
 * Plugin URI:  https://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version:     2.3.5
 * Author:      AudioTheme
 * Author URI:  https://audiotheme.com/
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: audiotheme
 * Domain Path: /languages
 * Requires at least: 4.3.1
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The plugin version.
 */
define( 'AUDIOTHEME_VERSION', '2.3.5' );

/**
 * Plugin path.
 */
if ( ! defined( 'AUDIOTHEME_DIR' ) ) {
	define( 'AUDIOTHEME_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Plugin URL.
 */
if ( ! defined( 'AUDIOTHEME_URI' ) ) {
	define( 'AUDIOTHEME_URI', plugin_dir_url( __FILE__ ) );
}

/**
 * Load the autoloader.
 */
if ( file_exists( AUDIOTHEME_DIR . 'vendor/autoload_52.php' ) ) {
	require( AUDIOTHEME_DIR . 'vendor/autoload_52.php' );
}

/**
 * Load functions and libraries.
 */
require( AUDIOTHEME_DIR . 'includes/default-filters.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'vendor/scribu/scb-framework/load.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated/discontinued.php' );

/**
 * Load admin functionality.
 */
if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'admin/functions.php' );
	require( AUDIOTHEME_DIR . 'includes/deprecated/deprecated-admin.php' );
	require( AUDIOTHEME_DIR . 'includes/deprecated/settings-screens.php' );
}

/**
 * Retrieve the AudioTheme plugin instance.
 *
 * @since 2.0.0
 *
 * @return AudioTheme_Plugin
 */
function audiotheme() {
	static $instance;

	if ( null === $instance ) {
		$instance = new AudioTheme_Plugin();
	}

	return $instance;
}

$audiotheme = audiotheme();

$audiotheme
	->set_basename( plugin_basename( __FILE__ ) )
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __FILE__ )
	->set_slug( 'audiotheme' )
	->set_url( plugin_dir_url( __FILE__ ) )
	->register_hooks( new AudioTheme_Provider_i18n() )
	->register_hooks( new AudioTheme_Provider_Setup() )
	->register_hooks( new AudioTheme_Provider_Widgets() )
	->register_hooks( new AudioTheme_Provider_Assets() )
	->register_hooks( new AudioTheme_Provider_GeneralHooks() )
	->register_hooks( new AudioTheme_Provider_JetpackCompatibility() )
	->register_hooks( new AudioTheme_Integration_YoastSEO() )
	->modules
	->register( new AudioTheme_Module_Archives( $audiotheme ) )
	->register( new AudioTheme_Module_Gigs( $audiotheme ) )
	->register( new AudioTheme_Module_Discography( $audiotheme ) )
	->register( new AudioTheme_Module_Videos( $audiotheme ) );

if ( is_admin() ) {
	$audiotheme
		->register_hooks( new AudioTheme_UpgradeManager() )
		->register_hooks( new AudioTheme_Provider_AdminHooks() )
		->register_hooks( new AudioTheme_AJAX_Admin() )
		->register_hooks( new AudioTheme_Provider_AdminAssets() )
		->register_hooks( new AudioTheme_Screen_Dashboard() )
		->register_hooks( new AudioTheme_Screen_Settings() )
		->register_hooks( new AudioTheme_Provider_Setting_GoogleMaps() );
}

if ( is_network_admin() ) {
	$audiotheme->register_hooks( new AudioTheme_Screen_Network_Settings() );
}

class AudioTheme_Gig_Query extends AudioTheme_Query_Gigs {}

/**
 * Load the plugin.
 *
 * @since 1.0.0
 */
function audiotheme_load() {
	audiotheme()->load();

	// Template hooks.
	add_action( 'audiotheme_template_include',    'audiotheme_template_setup' );
	add_action( 'audiotheme_before_main_content', 'audiotheme_before_main_content' );
	add_action( 'audiotheme_after_main_content',  'audiotheme_after_main_content' );
}
add_action( 'plugins_loaded', 'audiotheme_load' );
