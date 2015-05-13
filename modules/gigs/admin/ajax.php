<?php
/**
 * Search for venues that begin with a string.
 *
 * @since 1.0.0
 */
function audiotheme_ajax_get_venue_matches() {
	global $wpdb;

	$var    = $wpdb->esc_like( stripslashes( $_GET['term'] ) ) . '%';
	$sql    = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title LIKE %s ORDER BY post_title ASC", $var );
	$venues = $wpdb->get_col( $sql );

	wp_send_json( $venues );
}

/**
 * Check for an existing venue with the same name.
 *
 * @since 1.0.0
 */
function audiotheme_ajax_is_new_venue() {
	global $wpdb;

	$sql   = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title=%s ORDER BY post_title ASC LIMIT 1", stripslashes( $_GET['name'] ) );
	$venue = $wpdb->get_col( $sql );

	wp_send_json( $venue );
}
