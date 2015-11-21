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
 * Version:     1.9.0-beta
 * Author:      AudioTheme
 * Author URI:  https://audiotheme.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
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
 * The AudioTheme version.
 */
define( 'AUDIOTHEME_VERSION', '1.9.0-beta' );

/**
 * Framework path and URL.
 */
if ( ! defined( 'AUDIOTHEME_DIR' ) ) {
	define( 'AUDIOTHEME_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'AUDIOTHEME_URI' ) ) {
	define( 'AUDIOTHEME_URI', plugin_dir_url( __FILE__ ) );
}

/**
 * Load functions and libraries.
 */
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-assets.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-hooks-general.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-i18n.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-license.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-plugin.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-plugin-audiotheme.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-module-collection.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-module.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-posttype.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-setup.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-widgets.php' );
require( AUDIOTHEME_DIR . 'includes/default-filters.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/recent-posts.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/record.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/track.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/upcoming-gigs.php' );
require( AUDIOTHEME_DIR . 'includes/widgets/video.php' );
require( AUDIOTHEME_DIR . 'includes/vendor/scb/load.php' );
require( AUDIOTHEME_DIR . 'modules/archives/class-audiotheme-module-archives.php' );
require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-module-discography.php' );
require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-module-gigs.php' );
require( AUDIOTHEME_DIR . 'modules/videos/class-audiotheme-module-videos.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated/discontinued.php' );

/**
 * Load admin functionality.
 */
if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'admin/functions.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-assets-admin.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-hooks-admin.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-screen.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-screen-dashboard.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-screen-network-settings.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-screen-settings.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-setting-licensekey.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-updater.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-updater-plugin.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-updater-theme.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-updates.php' );
	require( AUDIOTHEME_DIR . 'admin/class-audiotheme-upgrade.php' );
	require( AUDIOTHEME_DIR . 'includes/deprecated/deprecated-admin.php' );
	require( AUDIOTHEME_DIR . 'includes/deprecated/settings-screens.php' );
}

/**
 * Retrieve the AudioTheme plugin instance.
 *
 * @since 1.9.0
 *
 * @return AudioTheme_PLugin
 */
function audiotheme() {
	static $instance;

	if ( null === $instance ) {
		$instance = new AudioTheme_Plugin_AudioTheme();
	}

	return $instance;
}

audiotheme()
	->set_directory( plugin_dir_path( __FILE__ ) )
	->set_file( __FILE__ )
	->set_slug( 'audiotheme' )
	->set_url( plugin_dir_url( __FILE__ ) )
	->register_hooks( new AudioTheme_i18n() )
	->register_hooks( new AudioTheme_Setup() )
	->register_hooks( new AudioTheme_Widgets() )
	->register_hooks( new AudioTheme_Assets() )
	->register_hooks( new AudioTheme_Hooks_General() )
	->modules
	->register( new AudioTheme_Module_Archives() )
	->register( new AudioTheme_Module_Gigs() )
	->register( new AudioTheme_Module_Discography() )
	->register( new AudioTheme_Module_Videos() );

if ( is_admin() ) {
	audiotheme()
		->register_hooks( new AudioTheme_Upgrade() )
		->register_hooks( new AudioTheme_Hooks_Admin() )
		->register_hooks( new AudioTheme_Updates() )
		->register_hooks( new AudioTheme_Assets_Admin() )
		->register_hooks( new AudioTheme_Screen_Dashboard() )
		->register_hooks( new AudioTheme_Screen_Settings() )
		->register_hooks( new AudioTheme_Setting_LicenseKey( audiotheme()->license ) );
}

if ( is_network_admin() ) {
	audiotheme()->register_hooks( new AudioTheme_Screen_Network_Settings() );
}

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
