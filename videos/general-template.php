<?php  
/**
 * Check if post has an video url supplied.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return bool Whether post has an video url supplied.
 */
function has_audiotheme_post_video( $post_id = null ) {
	return (bool) get_audiotheme_post_video_url( $post_id );
}


/**
 * Retrieve Post Video URL.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_post_video_url( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_video_url', true );
}


/**
 * Display Post Video.
 *
 * @since 1.0.0
 *
 * @param array $size Optional. Video size.  Defaults to 640 width.
 * @param string|array $attr Optional. Query string or array of attributes.
 */
function the_audiotheme_post_video( $size = array( 'width' => 640 ), $attr = '' ) {
	echo get_the_audiotheme_post_video( null, $size, $attr );
}


/**
 * Retrieve Post Video.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @param array $size Optional. Image size.  Defaults to 640 width.
 * @param string|array $attr Optional. Query string or array of attributes.
 */
function get_the_audiotheme_post_video( $post_id = null, $size = array( 'width' => 640 ), $attr = '' ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	$post_video_url = get_audiotheme_post_video_url( $post_id );
	$size = apply_filters( 'audiotheme_post_video_size', $size );
	
	$size = wp_parse_args( $size, $args );
	
	if ( $post_video_url ) {
	    $html = wp_oembed_get( esc_url( $post_video_url ), $size );
	} else {
	    $html = '';
	}
	
	return apply_filters( 'audiotheme_post_video_html', $html, $post_id, $post_video_url, $size, $attr );

}

?>