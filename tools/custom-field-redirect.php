<?php
/**
 * This code adapted from the Custom Field Redirect
 * plugin by Nathan Rice, http://www.nathanrice.net/plugins
 *
 * @package Genesis
 * @subpackage Custom Field Redirect
 */

if( ! function_exists( 'custom_field_redirect' ) ) {

// Hook the redirect function into the template_redirect action
// This part actually does the redirect, if necessary
add_action( 'template_redirect', 'custom_field_redirect' );
/**
 * Redirect a request to a post / page, if that item has a custom field
 * entry of 'redirect' and a value.
 *
 * @since Unknown
 *
 * @global mixed $wp_query
 */
function custom_field_redirect() {

	global $wp_query;

	$redirect = isset( $wp_query->post->ID ) ? get_post_meta( $wp_query->post->ID, 'redirect', true ) : '';

	if ( ! empty( $redirect ) && is_singular() ) {
		wp_redirect( esc_url_raw( $redirect ), 301 );
		exit();
	}
}

}