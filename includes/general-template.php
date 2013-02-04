<?php
/**
 * General template tags and functions.
 *
 * @package AudioTheme_Framework
 */

/**
 * Display a post type archive title.
 *
 * Just a wrapper to the default post_type_archive_title for the sake of
 * consistency. This should only be used in AudioTheme-specific template files.
 *
 * @since 1.0.0
 */
function the_audiotheme_archive_title() {
	post_type_archive_title();
}

/**
 * Display a post type archive description.
 *
 * @since 1.0.0
 *
 * @param string $before Content to display before the description.
 * @param string $after Content to display after the description.
 */
function the_audiotheme_archive_description( $before = '', $after = '' ) {
	if ( ! is_post_type_archive() ) {
		return;
	}

	$post_type_object = get_queried_object();

	if ( $archive_id = get_audiotheme_post_type_archive( $post_type_object->name ) ) {
		$archive = get_post( $archive_id );
		if ( ! empty( $archive->post_content ) ) {
			echo $before . apply_filters( 'the_content', $archive->post_content ) . $after;
		}
	}
}
