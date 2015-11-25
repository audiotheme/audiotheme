<?php
/**
 * Post type archives template functions.
 *
 * @package   AudioTheme\Template
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Get archive post IDs.
 *
 * @since 1.0.0
 *
 * @return array Associative array with post types as keys and post IDs as the values.
 */
function get_audiotheme_archive_ids() {
	return audiotheme()->modules['archives']->get_archive_ids();
}

/**
 * Get the archive post ID for a particular post type.
 *
 * @since 1.0.0
 *
 * @param string $post_type Optional. Post type name.
 * @return int|null
 */
function get_audiotheme_post_type_archive( $post_type = null ) {
	return audiotheme()->modules['archives']->get_archive_id( $post_type );
}

/**
 * Determine if the current template is a post type archive.
 *
 * @since 1.0.0
 *
 * @param array|string $post_types Optional. A post type name or array of
 *                                 post type names. Defaults to all archives
 *                                 registered via AudioTheme_PostType_Archive::add_post_type_archive().
 * @return bool
 */
function is_audiotheme_post_type_archive( $post_types = array() ) {
	return audiotheme()->modules['archives']->is_post_type_archive( $post_types );
}

/**
 * Determine if a post ID is for a post type archive post.
 *
 * @since 1.0.0
 *
 * @param int $archive_id Post ID.
 * @return string|bool Post type name if true, otherwise false.
 */
function is_audiotheme_post_type_archive_id( $archive_id ) {
	return audiotheme()->modules['archives']->is_archive_id( $archive_id );
}

/**
 * Retrieve archive meta.
 *
 * @since 1.0.0
 *
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool   $single Optional. Whether to return a single value.
 * @param mixed  $default Optional. A default value to return if the requested meta doesn't exist.
 * @param string $post_type Optional. The post type archive to retrieve meta data for. Defaults to the current post type.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function get_audiotheme_archive_meta( $key = '', $single = false, $default = null, $post_type = null ) {
	return audiotheme()->modules['archives']->get_archive_meta( $key, $single, $default, $post_type );
}

/**
 * Display classes for a wrapper div on an AudioTheme archive page.
 *
 * @since 1.2.1
 * @uses audiotheme_class()
 *
 * @param array|string $classes Optional. List of default classes as an array or space-separated string.
 * @param array|string $args Optional. Override defaults.
 * @return array
 */
function audiotheme_archive_class( $classes = array(), $args = array() ) {
	if ( ! empty( $classes ) && ! is_array( $classes ) ) {
		// Split a string.
		$classes = preg_split( '#\s+#', $classes );
	}

	if ( is_audiotheme_post_type_archive() ) {
		$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );
		$post_type_class = 'audiotheme-archive-' . str_replace( 'audiotheme_', '', $post_type );
		$classes = array_merge( $classes, array( 'audiotheme-archive', $post_type_class ) );
	}

	return audiotheme_class( 'archive', $classes, $args );
}
