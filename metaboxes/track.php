<?php
require_once('helper_functions.php');

/**
 * Add Track Metabox
 *
 * @since 1.0
 */
function audiotheme_add_track_meta(){
    add_meta_box( 
        'audiotheme-track-meta', 
        'Track Details', 
        'audiotheme_track_meta_cb', 
        'audiotheme_track', 
        'normal', 
        'high'
    );
}
add_action( 'add_meta_boxes', 'audiotheme_add_track_meta' );

/**
 * Track Metabox Callback
 *
 * - Track URL (_track_file_url)
 *
 * @since 1.0
 */
function audiotheme_track_meta_cb( $post ){
    //retrieve the metadata values if they exist
    $track_file = get_post_meta( $post->ID, '_track_file_url', true );
    $artist = get_post_meta( $post->ID, '_artist', true );
    $link = get_post_meta( $post->ID, '_track_link', true );
    
    /* Nonce to verify intention later */
	wp_nonce_field( 'save_audiotheme_track_meta', 'audiotheme_track_nonce' );
	
	audiotheme_meta_field( $post, 'url', '_track_file_url', 'Audio file URL' );
	audiotheme_meta_field( $post, 'text', '_artist', 'Artist' );
	audiotheme_meta_field( $post, 'text', '_track_link', 'Download Link', 'A link to download or purchase the track. Leave this empty if you don\'t want users to download the track.' );
}

/**
 * Save Metabox Values
 *
 * @since 1.0
 */
function audiotheme_track_save( $post_id ) {
    // Let's not auto save the data
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 

	// Check our nonce
	if( !isset( $_POST['audiotheme_track_nonce'] ) || !wp_verify_nonce( $_POST['audiotheme_track_nonce'], 'save_audiotheme_track_meta' ) ) return;

	// Make sure the current user can edit the post
	if( !current_user_can( 'edit_post' ) ) return;
	
    // Save metadata
    audiotheme_update_post_meta( $post_id, array('_track_file_url'), 'url' );
    audiotheme_update_post_meta( $post_id, array('_artist', '_track_link'), 'text' );

}
add_action( 'save_post', 'audiotheme_track_save' );

?>