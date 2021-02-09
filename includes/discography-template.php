<?php
/**
 * Discography template functions.
 *
 * @package   AudioTheme\Template
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Get record link sources.
 *
 * List of default outlets from which records can be purchased. The options
 * listed here show up as suggestions when the user types.
 *
 * @since 1.0.0
 *
 * @return array
 */
function get_audiotheme_record_link_sources() {
	$default_sources = array(
		'7digital' => array( 'icon' => '' ),
		'Amazon'   => array( 'icon' => '' ),
		'Bandcamp' => array( 'icon' => '' ),
		'CD Baby'  => array( 'icon' => '' ),
		'Google'   => array( 'icon' => '' ),
		'iTunes'   => array( 'icon' => '' ),
	);

	return apply_filters( 'audiotheme_record_link_sources', $default_sources );
}

/**
 * Get a record's type.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_record_type( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$type = get_the_terms( $post_id, 'audiotheme_record_type' );

	if ( empty( $type ) ) {
		return false; }

	$type = array_shift( $type );

	return $type->slug;
}

/**
 * Get a record's release year.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_record_release_year( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_release_year', true );
}

/**
 * Get a record's artist.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_record_artist( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_artist', true );
}

/**
 * Get a record's links.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_record_links( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	$links = array_filter( (array) get_post_meta( $post_id, '_audiotheme_record_links', true ) );
	return apply_filters( 'audiotheme_record_links', $links, $post_id );
}


/**
 * Get the record genre.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_record_genre( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_genre', true );
}

/**
 * Get a record's tracks.
 *
 * @since 1.0.0
 *
 * @param int   $post_id Post ID.
 * @param array $args Options to filter the results.
 * @return array
 */
function get_audiotheme_record_tracks( $post_id = null, $args = array() ) {
	$post = get_post( $post_id );

	$args = wp_parse_args( $args, array(
		'has_file' => false,
	) );

	$query = array(
		'post_type'   => 'audiotheme_track',
		'post_parent' => absint( $post->ID ),
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
		'numberposts' => -1,
	);

	// Only return tracks with a file URL.
	if ( $args['has_file'] ) {
		$query['meta_query'] = array(
			array(
				'key'     => '_audiotheme_file_url',
				'value'   => '',
				'compare' => '!=',
			),
		);
	}

	return get_posts( $query );
}

/**
 * Display a track title.
 *
 * @since 2.1.0
 *
 * @param  array   $args Optional. An array of arguments.
 */
function the_audiotheme_track_title( $args = array() ) {
	echo get_audiotheme_track_title( null, $args );
}

/**
 * Retrieve a linked title to the current track.
 *
 * The link isn't added if the record has disabled tracklist links.
 *
 * @since 2.1.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @param array      $args {
 *     Optional. An array of arguments.
 *
 *     string $before      Content to prepend to the link.
 *     string $after       Content to append to the link.
 *     string $before_link Content to prepend to text in the link tag.
 *     string $after_link  Content to append to text in the link tag.
 *     string $link_class  HTML class for the link tag.
 * }
 * @return string
 */
function get_audiotheme_track_title( $post = null, $args = array() ) {
	$post = get_post( $post );
	$disable_links = get_post_meta( $post->post_parent, '_audiotheme_disable_tracklist_links', true );

	$args = wp_parse_args( $args, array(
		'before'      => '',
		'after'       => '',
		'before_link' => '',
		'after_link'  => '',
		'link_class'  => 'track-title',
		'microdata'   => true,
	) );

	$output = $args['before'];

	if ( 'yes' !== $disable_links ) {
		$output .= sprintf(
			'<a href="%s" class="%s"%s>',
			esc_url( get_permalink( $post->ID ) ),
			esc_attr( $args['link_class'] ),
			$args['microdata'] ? ' itemprop="url"' : ''
		);
	}

	$output .= $args['before_link'] . get_the_title( $post ) . $args['after_link'];

	if ( 'yes' !== $disable_links ) {
		$output .= '</a>';
	}

	$output .= $args['after'];

	return $output;
}

/**
 * Check if a track is downloadable.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string|bool File url if downloadable, else false.
 */
function is_audiotheme_track_downloadable( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$is_downloadable = get_post_meta( $post_id, '_audiotheme_is_downloadable', true );

	$return = false;
	if ( $is_downloadable ) {
		$file_url = get_audiotheme_track_file_url( $post_id );

		if ( $file_url ) {
			$return = $file_url;
		}
	}

	return apply_filters( 'audiotheme_track_download_url', $return, $post_id );
}

/**
 * Get a track's artist.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 * @return string
 */
function get_audiotheme_track_artist( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_artist', true );
}

/**
 * Get the file URL for a track.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_track_file_url( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_file_url', true );
}

/**
 * Get the length of a track.
 *
 * @since 1.0.0
 * @todo Determine if the track's file is an attachment and check its meta data.
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_track_length( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_length', true );
}

/**
 * Get the purchase URL for a track.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_track_purchase_url( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_audiotheme_purchase_url', true );
}

/**
 * Get the track thumbnail ID.
 *
 * Fall back to the record featured image if the track doesn't have one.
 *
 * @since 1.0.0
 *
 * @param int|WP_Post $post Optional. Post ID or object.
 * @return int
 */
function get_audiotheme_track_thumbnail_id( $post = null ) {
	$post = get_post( $post );
	$thumbnail_id = 0;

	if ( has_post_thumbnail( $post->ID ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
	} elseif ( $post->post_parent && has_post_thumbnail( $post->post_parent ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post->post_parent );
	}

	return $thumbnail_id;
}

/**
 * Enqueue tracks.
 *
 * Saves basic track data to a global variable so it can be output as
 * JavaScript in the footer for use by scripts.
 *
 * If an associative array representing a track is passed, it should be wrapped
 * in an array itself. IDs and post objects can be passed by themselves or as an
 * array of IDs or objects.
 *
 * Example format of associative array:
 * <code>
 * $track = array(
 *     array(
 *         'title' => '',
 *         'file'  => '',
 *     )
 * )
 * </code>
 *
 * @since 1.1.0
 *
 * @see AudioTheme_PostType_Track::print_track_js()
 * @see AudioTheme_PostType_Track::prepare_track_for_js()
 * @global $audiotheme_enqueued_tracks
 *
 * @param int|array|object $track Accepts a track ID, record ID, post object, or array in the expected format.
 * @param string           $list A list identifier.
 */
function enqueue_audiotheme_tracks( $track, $list = 'tracks' ) {
	global $audiotheme_enqueued_tracks;

	$key = sanitize_key( $list );
	if ( ! isset( $audiotheme_enqueued_tracks[ $key ] ) ) {
		$audiotheme_enqueued_tracks[ $key ] = array();
	}

	$audiotheme_enqueued_tracks[ $key ] = array_merge( $audiotheme_enqueued_tracks[ $key ], (array) $track );
}

/**
 * Update a record's track count.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb
 *
 * @param int $post_id Record ID.
 */
function audiotheme_record_update_track_count( $post_id ) {
	global $wpdb;

	$sql = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'audiotheme_track' AND post_parent = %d AND ( post_status = 'publish' OR post_status = 'future' OR post_status = 'draft' OR post_status = 'pending' OR post_status = 'private' )";
	$track_count = $wpdb->get_var( $wpdb->prepare( $sql, $post_id ) );
	$track_count = empty( $track_count ) ? 0 : absint( $track_count );
	update_post_meta( $post_id, '_audiotheme_track_count', $track_count );
}
