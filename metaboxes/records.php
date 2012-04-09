<?php
/**
 * Add Metabox
 *
 * @since 1.0
 */
function audiotheme_add_record_meta() {
	
    add_meta_box( 
        'audiotheme-record-meta', 
        __( 'Record Details', 'audiotheme' ), 
        'audiotheme_record_meta_cb', 
        'audiotheme_record', 
        'normal', 
        'high'
    );
    
}
add_action( 'add_meta_boxes', 'audiotheme_add_record_meta' );

/**
 * Metabox Callback
 *
 * @since 1.0
 */
function audiotheme_record_meta_cb( $post ) {
	
	// Nonce to verify intention later
	wp_nonce_field( 'save_audiotheme_record_meta', 'audiotheme_record_nonce' );
	audiotheme_meta_field( $post, 'text', '_tracks', __( 'Tracks', 'audiotheme' ), __( 'For development. Comma separated list of track ID\'s', 'audiotheme' ) );
}

/**
 * Save Metabox Values
 *
 * @since 1.0
 */
function audiotheme_record_save( $post_id ) {
	
	// Let's not auto save the data
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 

	// Check our nonce
	if( ! isset( $_POST['audiotheme_record_nonce'] ) || ! wp_verify_nonce( $_POST['audiotheme_record_nonce'], 'save_audiotheme_record_meta' ) ) return;

	// Make sure the current user can edit the post
	if( ! current_user_can( 'edit_post' ) ) return;
	
	// Save metadata
	audiotheme_update_post_meta( $post_id, array( '_tracks' ), 'text' );

}
add_action( 'save_post', 'audiotheme_record_save' );

?>