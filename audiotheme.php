<?php
/**
 * Plugin Name: AudioTheme
 * Plugin URI: https://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version: 1.8.4
 * Author: AudioTheme
 * Author URI: https://audiotheme.com/
 * Requires at least: 4.0.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: audiotheme
 * Domain Path: /languages
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
 * @package AudioTheme_Framework
 * @version 1.8.4
 * @author AudioTheme
 * @link https://audiotheme.com/
 * @copyright Copyright 2012 AudioTheme
 * @license GPL-2.0+
*/

/**
 * The AudioTheme version.
 */
define( 'AUDIOTHEME_VERSION', '1.8.4' );

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
 * Load additional helper functions and libraries.
 */
require( AUDIOTHEME_DIR . 'includes/archives.php' );
require( AUDIOTHEME_DIR . 'includes/default-filters.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/less.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );
require( AUDIOTHEME_DIR . 'includes/media.php' );
require( AUDIOTHEME_DIR . 'includes/options.php' );
require( AUDIOTHEME_DIR . 'includes/widgets.php' );

/**
 * Load AudioTheme CPTs and corresponding functionality.
 */
require( AUDIOTHEME_DIR . 'modules/discography/discography.php' );
require( AUDIOTHEME_DIR . 'modules/gigs/gigs.php' );
require( AUDIOTHEME_DIR . 'modules/videos/videos.php' );

/**
 * AudioTheme setup.
 *
 * Begin setting up the framework during the after_setup_theme action.
 *
 * Ideally all functionality should be loaded via hooks so it can be disabled
 * or replaced by a theme or plugin if necessary.
 *
 * @since 1.0.0
 */
function audiotheme_load() {
	// Default hooks.
	add_action( 'init', 'audiotheme_register_scripts' );
	add_action( 'init', 'audiotheme_less_setup' );
	add_action( 'widgets_init', 'audiotheme_widgets_init' );
	add_action( 'wp_loaded', 'audiotheme_loaded' );
	add_action( 'audiotheme_template_include', 'audiotheme_template_setup' );

	add_filter( 'wp_nav_menu_objects', 'audiotheme_nav_menu_classes', 10, 3 );

	// Media hooks.
	add_action( 'init', 'audiotheme_add_default_oembed_providers' );
	add_filter( 'embed_oembed_html', 'audiotheme_oembed_html', 10, 4 );
	add_filter( 'embed_handler_html', 'audiotheme_oembed_html', 10, 4 );
	add_filter( 'video_embed_html', 'audiotheme_oembed_html', 10 ); // Jetpack compat.

	// Archive hooks.
	add_action( 'init', 'register_audiotheme_archives' );
	add_filter( 'post_type_link', 'audiotheme_archives_post_type_link', 10, 3 );
	add_filter( 'post_type_archive_link', 'audiotheme_archives_post_type_archive_link', 10, 2 );
	add_filter( 'post_type_archive_title', 'audiotheme_archives_post_type_archive_title' );
	add_filter( 'audiotheme_archive_title', 'audiotheme_archives_taxonomy_title' );

	add_action( 'admin_bar_menu', 'audiotheme_archives_admin_bar_edit_menu', 80 );
	add_action( 'post_updated', 'audiotheme_archives_post_updated', 10, 3 );
	add_action( 'delete_post', 'audiotheme_archives_deleted_post' );

	// Prevent the audiotheme_archive post type rules from being registered.
	add_filter( 'audiotheme_archive_rewrite_rules', '__return_empty_array' );

	// Load discography.
	add_action( 'init', 'audiotheme_discography_init' );

	// Load gigs.
	add_action( 'init', 'audiotheme_gigs_init' );

	// Load videos.
	add_action( 'init', 'audiotheme_videos_init' );

	// Template hooks.
	add_action( 'audiotheme_before_main_content', 'audiotheme_before_main_content' );
	add_action( 'audiotheme_after_main_content', 'audiotheme_after_main_content' );

	// Deprecated.
	add_filter( 'dynamic_sidebar_params', 'audiotheme_widget_count_class' );
	add_filter( 'get_pages', 'audiotheme_page_list' );
	add_filter( 'page_css_class', 'audiotheme_page_list_classes', 10, 2 );
	add_filter( 'nav_menu_css_class', 'audiotheme_nav_menu_name_class', 10, 2 );
}
add_action( 'after_setup_theme', 'audiotheme_load', 5 );

/**
 * Additional setup during init.
 *
 * @since 1.2.0
 */
function audiotheme_init() {
	if ( current_theme_supports( 'audiotheme-post-gallery' ) ) {
		// High priority so plugins filtering ouput don't get stomped. Jetpack, etc.
		add_filter( 'post_gallery', 'audiotheme_post_gallery', 5000, 2 );
	}
}
add_action( 'init', 'audiotheme_init' );

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

	if ( is_admin() ) {
		// Load discography admin.
		add_action( 'init', 'audiotheme_load_discography_admin' );

		// Load gigs admin.
		add_action( 'init', 'audiotheme_gigs_admin_setup' );

		// Load videos admin.
		add_action( 'init', 'audiotheme_load_videos_admin' );
	}
}
add_action( 'after_setup_theme', 'audiotheme_load_admin', 5 );

/**
 * Register frontend scripts and styles for enqueuing when needed.
 *
 * @since 1.0.0
 * @link https://core.trac.wordpress.org/ticket/18909
 */
function audiotheme_register_scripts() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script( 'audiotheme', AUDIOTHEME_URI . 'includes/js/audiotheme' . $suffix . '.js', array( 'jquery', 'jquery-jplayer', 'jquery-fitvids' ), '1.0', true );
	wp_register_script( 'jquery-fitvids', AUDIOTHEME_URI . 'includes/js/jquery.fitvids.min.js', array( 'jquery' ), '1.1.0', true );
	wp_register_script( 'jquery-jplayer', AUDIOTHEME_URI . 'includes/js/jquery.jplayer.min.js', array( 'jquery' ), '2.9.2', true );
	wp_register_script( 'jquery-jplayer-playlist', AUDIOTHEME_URI . 'includes/js/jplayer.playlist.min.js', array( 'jquery-jplayer' ), '2.9.2', true );
	wp_register_script( 'jquery-placeholder', AUDIOTHEME_URI . 'includes/js/jquery.placeholder.min.js', array( 'jquery' ), '2.0.7', true );
	wp_register_script( 'jquery-timepicker', AUDIOTHEME_URI . 'includes/js/jquery.timepicker.min.js', array( 'jquery' ), '1.6.11', true );

	wp_localize_script( 'jquery-jplayer', 'AudiothemeJplayer', array(
		'swfPath' => AUDIOTHEME_URI . 'includes/js'
	) );

	wp_register_style( 'audiotheme', AUDIOTHEME_URI . 'includes/css/audiotheme.min.css' );
}

/**
 * Support localization for the plugin strings.
 *
 * @since 1.0.0
 */
function audiotheme_load_textdomain() {
	load_plugin_textdomain( 'audiotheme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'audiotheme_load_textdomain' );

/**
 * Flush the rewrite rules if needed.
 *
 * @since 1.0.0
 */
function audiotheme_loaded() {
	if ( ! is_network_admin() && 'no' !== get_option( 'audiotheme_flush_rewrite_rules' ) ) {
		update_option( 'audiotheme_flush_rewrite_rules', 'no' );
		flush_rewrite_rules();
	}
}

/**
 * Activation routine.
 *
 * Occurs too late to flush rewrite rules, so set an option to flush the
 * rewrite rules on the next request.
 *
 * @since 1.0.0
 */
function audiotheme_activate() {
	update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
}
register_activation_hook( __FILE__, 'audiotheme_activate' );

/**
 * Deactivation routine.
 *
 * Deleting the rewrite rules option should force them to be regenerated the
 * next time they're needed.
 *
 * @since 1.0.0
 */
function audiotheme_deactivate() {
	delete_option( 'rewrite_rules' );
}
register_deactivation_hook( __FILE__, 'audiotheme_deactivate' );
