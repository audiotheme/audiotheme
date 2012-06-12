<?php 
/**
 * Check if post has an file url supplied.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return bool Whether post has an video url supplied.
 */
function audiotheme_track_has_download( $post_id ) {
	
	$return = false;
	
	$allow_download = get_post_meta( $post_id, '_allow_download', true );
	
	if ( $allow_download ) {
		$file_url = get_audiotheme_track_file_url( $post_id );
		if ( $file_url && false === strpos( $file_url, 'spotify:' ) ) {
			$return = $file_url;
		}
	}
	
	return apply_filters( 'audiotheme_track_download_url', $return, $post_id );

}

 
/**
 * Check if post has an file url supplied.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return bool Whether post has an video url supplied.
 */
function has_audiotheme_track_file( $post_id = null ) {
	return (bool) get_audiotheme_track_file_url( $post_id );
}


/**
 * Retrieve Track File URL.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_track_file_url( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_file_url', true );
}


?>