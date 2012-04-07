<?php
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
 * - Track URL (_track_url)
 *
 * @since 1.0
 */
function audiotheme_track_meta_cb( $post ){
    //retrieve the metadata values if they exist
    $track_url = get_post_meta( $post->ID, '_track_url', true );
    
    /* Nonce to verify intention later */
	wp_nonce_field( 'save_audiotheme_track_meta', 'audiotheme_track_nonce' );
    ?>
    
    <p>
        <label for="track_url">Track URL</label>
        <input type="text" id="track_url" name="_track_url" value="<?php echo esc_attr( $track_url ); ?>" />
    </p>
    
<?php 
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
	
    //verify the metadata is set
    if ( isset( $_POST['_track_url'] ) ) {
        //save the metadata
        update_post_meta( $post_id, '_track_url', strip_tags( $_POST['_track_url'] ) ); 
    }
}
add_action( 'save_post', 'audiotheme_track_save' );

?>