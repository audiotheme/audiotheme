<?php
/**
 * Check if the framework has been installed as a theme.
 *
 * This file won't be used by the framework unless it is installed as a theme.
 * It then serves as the standard functions.php packaged with themes. It will
 * display an alert to users who mistakenly install the framework as a theme and
 * give them the option to move it automatically if possible.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the translation files as a theme.
 *
 * @since 1.2.0
 */
function audiotheme_not_a_theme_setup_as_theme() {
	load_theme_textdomain( 'audiotheme', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'audiotheme_not_a_theme_setup_as_theme' );

/**
 * Move the framework to the plugins directory.
 *
 * @since 1.2.0
 */
function audiotheme_not_a_theme() {
	global $wp_filesystem;

	if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'audiotheme-theme-to-plugin' ) ) {
		return false;
	}

	$move_url = wp_nonce_url( 'themes.php', 'audiotheme-theme-to-plugin' );

	if ( false === ( $credentials = request_filesystem_credentials( $move_url ) ) ) {
		return true;
	}

	if ( ! WP_Filesystem( $credentials ) ) {
		// Credentials weren't good, ask again.
		request_filesystem_credentials( $move_url );
		return true;
	}

	$plugin_dir = $wp_filesystem->wp_plugins_dir() . 'audiotheme/';
	$theme_dir  = trailingslashit( get_template_directory() );

	// Check if the framework plugin directory already exists.
	if ( is_dir( $plugin_dir ) ) {
		$redirect = add_query_arg( 'atmovemsg', 'plugin-exists', admin_url( 'themes.php' ) );
		wp_safe_redirect( esc_url_raw( $redirect ) );
		exit;
	}

	// Move the plugin.
	if ( $wp_filesystem->move( $theme_dir, $plugin_dir ) ) {
		// @todo Any way to re-activate the previous theme?
		wp_safe_redirect( esc_url_raw( admin_url( 'plugins.php' ) ) );
		exit;
	}

	// Redirect to notice saying it didn't work. Move it manually.
	else {
		$redirect = add_query_arg( 'atmovemsg', 'move-failed', admin_url( 'themes.php' ) );
		wp_safe_redirect( esc_url_raw( $redirect ) );
		exit;
	}
}
add_action( 'admin_init', 'audiotheme_not_a_theme' );

/**
 * Display a notice in the dashboard to alert the user that the framework is not a theme.
 *
 * @since 1.2.0
 */
function audiotheme_not_a_theme_notice() {
	$notice     = '';
	$message_id = ( isset( $_REQUEST['atmovemsg'] ) ) ? $_REQUEST['atmovemsg'] : '';
	$move_url   = wp_nonce_url( 'themes.php', 'audiotheme-theme-to-plugin' );

	switch ( $message_id ) {
		case 'plugin-exists' :
			if ( ! is_multisite() && current_user_can( 'delete_themes' ) ) {
				$stylesheet = get_template();
				$delete_link = sprintf(
					__( 'You should <a href="%s">delete the theme</a> and activate as a plugin instead.', 'audiotheme' ),
					wp_nonce_url( 'themes.php?action=delete&amp;stylesheet=' . urlencode( $stylesheet ), 'delete-theme_' . $stylesheet )
				);
			}

			$notice  = __( 'The AudioTheme framework appears to already exist as a plugin.', 'audiotheme' );
			$notice .= empty( $delete_link ) ? '' : ' ' . $delete_link;
			break;
		case 'move-failed' :
			$notice  = __( 'The AudioTheme framework could not be moved to your plugins folder automatically. You should move it manually.', 'audiotheme' );
			break;
		default :
			$notice  = __( '<strong>The AudioTheme framework is not a theme.</strong> It should be installed as a plugin.', 'audiotheme' );
			$notice .= current_user_can( 'install_plugins' ) ? sprintf( ' <a href="%s">%s</a>', esc_url( $move_url ), __( 'Would you like to move it now?', 'audiotheme' ) ) : '';
	}

	if ( ! empty( $notice ) ) :
		?>
		<div class="error">
			<p><?php echo $notice; ?></p>
		</div>
		<?php
	endif;
}
add_action( 'admin_notices', 'audiotheme_not_a_theme_notice' );
