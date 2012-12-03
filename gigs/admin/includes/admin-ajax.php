<?php
/**
 * Search for venues that begin with a string.
 *
 * @since 1.0.0
 */
function ajax_get_audiotheme_venue_matches() {
	global $wpdb;
	
	$var = like_escape( stripslashes( $_GET['name'] ) ) . '%';
	$venues = $wpdb->get_col( $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title LIKE %s ORDER BY post_title ASC", $var ) );
	
	wp_send_json( $venues );
}

/**
 * Check for an existing venue with the same name.
 *
 * @since 1.0.0
 */
function ajax_is_new_audiotheme_venue() {
	global $wpdb;
	
	$venue = $wpdb->get_col( $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title=%s ORDER BY post_title ASC LIMIT 1", stripslashes( $_GET['name'] ) ) );
	
	wp_send_json( $venue );
}
?>