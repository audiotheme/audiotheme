<?php
/**
 * Plugin Name: AudioTheme
 * Plugin URI: https://audiotheme.com/view/audiotheme/
 * Description: A platform for music-oriented websites, allowing for easy management of gigs, discography, videos and more.
 * Version: 1.9.0-beta
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
 * Load additional helper functions and libraries.
 */
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-i18n.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-license.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-plugin.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-plugin-audiotheme.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-module-collection.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-module.php' );
require( AUDIOTHEME_DIR . 'includes/class-audiotheme-posttype.php' );
require( AUDIOTHEME_DIR . 'includes/default-filters.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/less.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );
require( AUDIOTHEME_DIR . 'includes/media.php' );
require( AUDIOTHEME_DIR . 'includes/widgets.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated/deprecated.php' );
require( AUDIOTHEME_DIR . 'includes/deprecated/options.php' );

/**
 * Load modules.
 */
require( AUDIOTHEME_DIR . 'modules/archives/class-audiotheme-module-archives.php' );
require( AUDIOTHEME_DIR . 'modules/discography/class-audiotheme-module-discography.php' );
require( AUDIOTHEME_DIR . 'modules/gigs/class-audiotheme-module-gigs.php' );
require( AUDIOTHEME_DIR . 'modules/videos/class-audiotheme-module-videos.php' );

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
	add_action( 'init', 'audiotheme_register_scripts' );
	add_action( 'init', 'audiotheme_less_setup' );
	add_action( 'widgets_init', 'audiotheme_widgets_init' );
	add_action( 'wp_loaded', 'audiotheme_loaded' );
	add_action( 'audiotheme_template_include', 'audiotheme_template_setup' );

	add_filter( 'audiotheme_archive_title', 'audiotheme_archives_taxonomy_title' );
	add_filter( 'wp_nav_menu_objects', 'audiotheme_nav_menu_classes', 10, 3 );

	// Media hooks.
	add_filter( 'wp_image_editors', 'audiotheme_register_image_editors' );
	add_filter( 'embed_oembed_html', 'audiotheme_oembed_html', 10, 4 );
	add_filter( 'embed_handler_html', 'audiotheme_oembed_html', 10, 4 );
	add_filter( 'video_embed_html', 'audiotheme_oembed_html', 10 ); // Jetpack compat.
	add_filter( 'wp_prepare_attachment_for_js', 'audiotheme_wp_prepare_audio_attachment_for_js', 10, 3 );

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
}
add_action( 'after_setup_theme', 'audiotheme_load_admin', 5 );

/**
 * Register frontend scripts and styles for enqueuing when needed.
 *
 * @since 1.0.0
 * @link https://core.trac.wordpress.org/ticket/18909
 */
function audiotheme_register_scripts() {
	global $wp_locale;

	$base_url = set_url_scheme( AUDIOTHEME_URI . '/includes/js' );
	$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_register_script( 'audiotheme', $base_url  .'/audiotheme' . $suffix . '.js', array( 'jquery', 'jquery-jplayer', 'jquery-fitvids' ), '1.0', true );
	wp_register_script( 'jquery-fitvids', $base_url  .'/vendor/jquery.fitvids.min.js', array( 'jquery' ), '1.1.0', true );
	wp_register_script( 'jquery-jplayer', $base_url  .'/vendor/jquery.jplayer.min.js', array( 'jquery' ), '2.2.19', true );
	wp_register_script( 'jquery-jplayer-playlist', $base_url  .'/vendor/jquery.jplayer.playlist.min.js', array( 'jquery-jplayer' ), '2.2.2', true );
	wp_register_script( 'jquery-placeholder', $base_url  .'/vendor/jquery.placeholder.min.js', array( 'jquery' ), '2.0.7', true );
	wp_register_script( 'jquery-timepicker', $base_url  .'/vendor/jquery.timepicker.min.js', array( 'jquery' ), '1.6.11', true );
	wp_register_script( 'moment', $base_url  .'/vendor/moment.min.js', array(), '2.10.6', true );
	wp_register_script( 'pikaday', $base_url  .'/vendor/pikaday.min.js', array( 'moment'), '1.4.0', true );

	wp_localize_script( 'jquery-jplayer', 'AudiothemeJplayer', array(
		'swfPath' => $base_url . '/vendor',
	) );

	wp_localize_script( 'pikaday', '_pikadayL10n', array(
		'previousMonth' => __( 'Previous Month', 'audiotheme' ),
		'nextMonth'     => __( 'Next Month', 'audiotheme' ),
		'months'        => array_values( $wp_locale->month ),
		'weekdays'      => $wp_locale->weekday,
		'weekdaysShort' => array_values( $wp_locale->weekday_abbrev ),
	) );

	wp_register_style( 'audiotheme', AUDIOTHEME_URI . 'includes/css/audiotheme.min.css' );
}

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
