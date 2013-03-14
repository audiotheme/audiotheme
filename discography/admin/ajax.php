<?php
/**
 * Create a default track for use in the tracklist repeater.
 *
 * @since 1.0.0
 */
function audiotheme_ajax_get_default_track() {
	$is_valid_nonce = ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'get-default-track_' . $_POST['record'] );

	if ( empty( $_POST['record'] ) || ! $is_valid_nonce ) {
		wp_send_json_error();
	}

	$data['track'] = get_default_post_to_edit( 'audiotheme_track', true );
	$data['nonce'] = wp_create_nonce( 'get-default-track_' . $_POST['record'] );

	wp_send_json( $data );
}
