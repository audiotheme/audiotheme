<?php
/**
 * Discography template functions.
 *
 * @package AudioTheme_Framework
 * @subpackage Template
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
 * Get record type strings.
 *
 * List of default record types to better define the record, much like a post
 * format.
 *
 * @since 1.0.0
 *
 * @return array List of record types.
 */
function get_audiotheme_record_type_strings() {
	$strings = array(
		'record-type-album'  => _x( 'Album',  'Record type', 'audiotheme' ),
		'record-type-single' => _x( 'Single', 'Record type', 'audiotheme' ),
	);

	/**
	 * Filter the list of available of record types.
	 *
	 * Terms will be registered automatically for new record types. Keys must
	 * be prefixed with 'record-type'.
	 *
	 * @since x.x.x
	 *
	 * @param array strings List of record types. Keys must be prefixed with 'record-type-'.
	 */
	return apply_filters( 'audiotheme_record_type_strings', $strings );
}

/**
 * Get record type slugs.
 *
 * Gets an array of available record type slugs from record type strings.
 *
 * @since 1.0.0
 *
 * @return array List of record type slugs.
 */
function get_audiotheme_record_type_slugs() {
	$slugs = array_keys( get_audiotheme_record_type_strings() );
	return $slugs;
}

/**
 * Get record type string.
 *
 * Sets default value of record type if option is not set.
 *
 * @since 1.0.0
 *
 * @param string Record type slug.
 * @return string Record type label.
 */
function get_audiotheme_record_type_string( $slug ) {
	$strings = get_audiotheme_record_type_strings();

	if ( ! $slug ) {
		return $strings['record-type-album'];
	} else {
		return ( isset( $strings[ $slug ] ) ) ? $strings[ $slug ] : '';
	}
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

	if ( empty( $type ) )
		return false;

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
	return get_post_meta( $post_id, '_audiotheme_record_links', true );
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
 * @param int $post_id Post ID.
 * @param array $args
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

	$tracks = get_posts( $query );

    if ( ! $tracks ) {
        $tracks = false;
    }

    return $tracks;
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
 * @param int $post_id. Post ID.
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
 * @param int $post_id Optional. Post ID.
 * @return int
 */
function get_audiotheme_track_thumbnail_id( $post = null ) {
	$post = get_post( $post );
	$thumbnail_id = 0;

	if ( has_post_thumbnail( $post->ID ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
	} elseif ( $post->post_parent && has_post_thumbnail( $post->post_parent) ) {
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
 * @uses $audiotheme_enqueued_tracks
 * @see audiotheme_print_tracks_js()
 * @see audiotheme_prepare_track_for_js()
 *
 * @param int|array|object $track Accepts a track ID, record ID, post object, or array in the expected format.
 * @param string $list A list identifier.
 */
function enqueue_audiotheme_tracks( $track, $list = 'tracks' ) {
	global $audiotheme_enqueued_tracks;

	$key = sanitize_key( $list );
	if ( ! isset( $audiotheme_enqueued_tracks[ $key ] ) ) {
		$audiotheme_enqueued_tracks[ $key ] = array();
	}

	$audiotheme_enqueued_tracks[ $key ] = array_merge( $audiotheme_enqueued_tracks[ $key ], (array) $track );
}
