<?php
/**
 * Plugin Name: AudioTheme
 * Plugin URI: https://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version: 1.9.0-beta
 * Author: AudioTheme
 * Author URI: https://audiotheme.com/
 * License: GPL-2.0+
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
 *
 * @package AudioTheme
 * @version 1.9.0-beta
 * @author AudioTheme
 * @link https://audiotheme.com/
 * @copyright Copyright 2012 AudioTheme
 * @license GPL-2.0+
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
require( AUDIOTHEME_DIR . 'includes/deprecated/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated/discontinued.php' );

/**
 * Load modules.
 */
require( AUDIOTHEME_DIR . 'modules/archives/class-audiotheme-module-archives.php' );
require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-module-discography.php' );
require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-module-gigs.php' );
require( AUDIOTHEME_DIR . 'modules/videos/class-audiotheme-module-videos.php' );

/**
 * Load admin functionality.
 */
if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'includes/deprecated/deprecated-admin.php' );
	require( AUDIOTHEME_DIR . 'includes/deprecated/settings-screens.php' );
	require( AUDIOTHEME_DIR . 'admin/functions.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-admin-assets.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen-dashboard.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen-network-settings.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-screen-settings.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-setting-licensekey.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-plugin.php' );
	require( AUDIOTHEME_DIR . 'admin/includes/class-audiotheme-updater-theme.php' );
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

/**
 * AudioTheme setup.
 *
 * Begin setting up the framework during the after_setup_theme action.
 *
 * @since 1.0.0
 */
function audiotheme_load() {
	audiotheme()->load();

	// Default hooks.
	add_filter( 'audiotheme_archive_title', 'audiotheme_archives_taxonomy_title' );
	add_filter( 'wp_nav_menu_objects', 'audiotheme_nav_menu_classes', 10, 3 );

	// Template hooks.
	add_action( 'audiotheme_template_include', 'audiotheme_template_setup' );
	add_action( 'audiotheme_before_main_content', 'audiotheme_before_main_content' );
	add_action( 'audiotheme_after_main_content', 'audiotheme_after_main_content' );

	// Deprecated.
	add_action( 'init', 'audiotheme_less_setup' );
	add_filter( 'embed_oembed_html', 'audiotheme_oembed_html', 10, 4 );
	add_filter( 'embed_handler_html', 'audiotheme_oembed_html', 10, 4 );
	add_filter( 'video_embed_html', 'audiotheme_oembed_html', 10 ); // Jetpack compat.
	add_filter( 'dynamic_sidebar_params', 'audiotheme_widget_count_class' );
	add_filter( 'get_pages', 'audiotheme_page_list' );
	add_filter( 'page_css_class', 'audiotheme_page_list_classes', 10, 2 );
	add_filter( 'nav_menu_css_class', 'audiotheme_nav_menu_name_class', 10, 2 );
}
add_action( 'after_setup_theme', 'audiotheme_load', 5 );

/**
 * Load admin-specific functions and libraries.
 *
 * Has to be loaded after the Theme Customizer in order to determine if the
 * Settings API should be included while customizing a theme.
 *
 * @since 1.0.0
 */
function audiotheme_load_admin() {
	global $wp_customize;

	if ( is_admin() || ( $wp_customize && $wp_customize->is_preview() ) ) {
		require( AUDIOTHEME_DIR . 'admin/admin.php' );
		audiotheme_admin_setup();
	}
}
add_action( 'after_setup_theme', 'audiotheme_load_admin', 5 );
