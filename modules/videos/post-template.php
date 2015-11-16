<?php
/**
 * Video template functions.
 *
 * @package AudioTheme\Template
 * @since 1.0.0
 */

/**
 * Retrieve a video URL.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_video_url( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_video_url', true );
}

/**
 * Display a video.
 *
 * @since 1.0.0
 *
 * @param array $args Optional. (width, height)
 * @param array $query_args Optional. Provider specific parameters.
 */
function the_audiotheme_video( $args = array(), $query_args = array() ) {
	echo get_audiotheme_video( get_the_ID(), $args, $query_args );
}

/**
 * Retrieve a video.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @param array $args Optional. (width, height)
 * @param array $query_args Optional. Provider specific parameters.
 * @return string Video HTML
 */
function get_audiotheme_video( $post_id = null, $args = array(), $query_args = array() ) {
	global $wp_embed;

	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	$video_url = get_audiotheme_video_url( $post_id );

	$html = '';
	if ( $video_url ) {
		// Save current embed settings and restore them after running the shortcode.
		$restore_post_ID = $wp_embed->post_ID;
		$restore_linkifunknown = $wp_embed->linkifunknown;
		$restore_usecache = $wp_embed->usecache;

		// Can't be sure what the embed settings are, so explicitly set them.
		$wp_embed->post_ID = $post_id; // Allows WP_Embed caching to work when this function is called outside of the loop.
		$wp_embed->linkifunknown = false;
		$wp_embed->usecache = true;

		$html = $wp_embed->shortcode( $args, add_query_arg( $query_args, $video_url ) );

		// Restore original embed settings.
		$wp_embed->post_ID = $restore_post_ID;
		$wp_embed->linkifunknown = $restore_linkifunknown;
		$wp_embed->usecache = $restore_usecache;
	}

	if ( false !== strpos( $html, '[video' ) ) {
		$html = do_shortcode( $html );
	}

	return apply_filters( 'audiotheme_video_html', $html, $post_id, $video_url, $args, $query_args );
}
