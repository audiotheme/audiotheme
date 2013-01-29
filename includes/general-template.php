<?php
/**
 * General template tags and functions.
 *
 * @package AudioTheme_Framework
 */

/**
 * Get the archive page for a post type.
 *
 * @since 1.0.0
 *
 * @param string $post_type Feature identifier.
 * @return WP_Post The archive page or null if one hasn't been set.
 */
function get_audiotheme_archive_page( $post_type ) {
	$archive_pages = get_option( 'audiotheme_archive_pages' );
	return ( empty( $archive_pages[ $post_type ] ) ) ? null : get_post( $archive_pages[ $post_type ] );
}

/**
 * Get the archive page slug for a post type.
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type.
 * @return string Page slug or null if an archive page hasn't been set.
 */
function get_audiotheme_archive_page_slug( $post_type ) {
	$archive_pages = get_option( 'audiotheme_archive_pages' );
	return ( empty( $archive_pages[ $post_type ] ) ) ? null : get_page_uri( $archive_pages[ $post_type ] );
}

/**
 * Get all the archive page IDs.
 *
 * @since 1.0.0
 *
 * @return array List of page IDs.
 */
function get_audiotheme_archive_page_ids() {
	return (array) get_option( 'audiotheme_archive_pages' );
}

/**
 * Get the feature identifier for a given archive page.
 *
 * @since 1.0.0
 *
 * @return string
 */
function get_audiotheme_archive_post_type_by_page_id( $id ) {
	$archive_pages = (array) get_option( 'audiotheme_archive_pages' );
	return array_search( $id, $archive_pages );
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

	if ( $page = get_audiotheme_archive_page( $post_type_object->name ) ) {
		if ( ! empty( $page->post_content ) ) {
			echo $before . apply_filters( 'the_content', $page->post_content ) . $after;
		}
	}
}
