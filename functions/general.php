<?php
/**
 * This is where we put all the functions that that are
 * difficult or impossible to categorize anywhere else.
 *
 * @package Genesis
 **/

/**
 * Helper function to enable the author box for ALL users.
 *
 * @since 1.4.1
 */
function genesis_enable_author_box( $args = array() ) {

	$args = wp_parse_args( $args, array( 'type' => 'single' ) );

	if ( $args['type'] === 'single' ) {
		add_filter( 'get_the_author_genesis_author_box_single', '__return_true' );
	}
	elseif ( $args['type'] === 'archive' ) {
		add_filter( 'get_the_author_genesis_author_box_archive', '__return_true' );
	}

}

/**
 * This function redirects the user to an admin page, and adds query args
 * to the URL string for alerts, etc.
 *
 * @since 1.6
 */
function genesis_admin_redirect( $page, $query_args = array() ) {

	if ( ! $page )
		return;

	$url = menu_page_url( $page, false );

	foreach ( (array) $query_args as $key => $value ) {
		if ( isset( $key ) && isset( $value ) ) {
			$url = add_query_arg( $key, $value, $url );
		}
	}

	wp_redirect( esc_url_raw( $url ) );

}

/**
 * Detect Plugin by constant, class or function existence.
 *
 * Detect Plugin from a list of constants, classes or functions added by plugins.
 *
 * @since 1.6
 * @author Charles Clarkson
 *
 * @param array $plugins Array of Array for constants, classes and / or functions to check for plugin existence.
 * @return boolean True if plugin exists or false if plugin constant, class or function not detected.
 */
function genesis_detect_plugin( $plugins ) {

	// Check for classes
	if ( isset( $plugins['classes'] ) ) {
		foreach ( $plugins['classes'] as $name ) {
			if ( class_exists($name) )
				return true;
		}
	}

	// Check for functions
	if ( isset( $plugins['functions'] ) ) {
		foreach ( $plugins['functions'] as $name ) {
			if ( function_exists($name) )
				return true;
		}
	}

	// Check for constants
	if ( isset( $plugins['constants'] ) ) {
		foreach ( $plugins['constants'] as $name ) {
			if ( defined($name) )
				return true;
		}
	}

	return false;
}