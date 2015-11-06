<?php
/**
 * Loads the Posts to Posts library and dependencies.
 *
 * @package AudioTheme
 * @link https://github.com/AppThemes/wp-posts-to-posts-core
 * @link https://github.com/AppThemes/wp-posts-to-posts-core/wiki/Bundling-in-a-plugin
 */

/**
 * Include the scbFramework.
 */
require( AUDIOTHEME_DIR . 'includes/vendor/scb/load.php' );

/**
 * Attach hook to load the Posts to Posts core.
 *
 * This doesn't actually occur during the init hook despite the name.
 *
 * @since 1.0.0
 */
function audiotheme_p2p_init() {
	add_action( 'plugins_loaded', 'audiotheme_p2p_load_core', 20 );
}
scb_init( 'audiotheme_p2p_init' );

/**
 * Load Posts 2 Posts core.
 *
 * Requires the scbFramework.
 *
 * Posts 2 Posts requires two custom database tables to store post
 * relationships and relationship metadata. If an alternative version of the
 * library doesn't exist, the tables are created on admin_init.
 *
 * @since 1.0.0
 */
function audiotheme_p2p_load_core() {
	if ( function_exists( 'p2p_register_connection_type' ) ) {
		return;
	}

	if ( ! defined( 'P2P_TEXTDOMAIN' ) ) {
		define( 'P2P_TEXTDOMAIN', 'audiotheme' );
	}

	require_once( AUDIOTHEME_DIR . 'includes/vendor/p2p/init.php' );

	add_action( 'admin_init', array( 'P2P_Storage', 'install' ) );
}
