<?php
/**
 * Create a default track for use in the tracklist repeater.
 *
 * @since 1.0.0
 *
 * @todo Check to be sure it's a POST request and manage a nonce.
 */
function audiotheme_ajax_get_default_track() {
	$track = get_default_post_to_edit( 'audiotheme_track', true );
	
	wp_send_json( $track );
}