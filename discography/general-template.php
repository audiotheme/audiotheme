<?php
/**
 * Record Link Sources
 *
 * List of default resources in which records can be purchased.
 * The options listed here show up as suggestions when the user types.
 *
 * @return array
 * @since 1.0.0
 */
function get_audiotheme_record_link_sources() {
	$default_sources = array(
		'7digital'   => array( 'icon' => '' ),
		'Amazon'     => array( 'icon' => '' ),
		'Bandcamp'   => array( 'icon' => '' ),
		'CD Baby'    => array( 'icon' => '' ),
		'Google'     => array( 'icon' => '' ),
		'iTunes'     => array( 'icon' => '' )
	);
	
	return apply_filters( 'audiotheme_record_link_sources', $default_sources );
}


/**
 * Record Type Strings
 *
 * List of default record types to better define the record, much like a post format.
 *
 * @return array
 * @since 1.0.0
 */
function get_audiotheme_record_type_strings() {
	$strings = array(
		'record-type-album'  => _x( 'Album',  'Record type', 'audiotheme-i18n' ),
		'record-type-single' => _x( 'Single', 'Record type', 'audiotheme-i18n' )
	);
	return $strings;
}


/**
 * Get Record Type Slugs
 *
 * Gets and sets an array of available record type slugs from record type strings.
 *
 * @return array
 * @since 1.0.0
 */
function get_audiotheme_record_type_slugs() {
	$slugs = array_keys( get_audiotheme_record_type_strings() );
	return array_combine( $slugs, $slugs );
}


/**
 * Get Record Type String
 *
 * Sets default value of record type if option is not set.
 *
 * @return array
 * @since 1.0.0
 */
function get_audiotheme_record_type_string( $slug ) {
	$strings = get_audiotheme_record_type_slugs();
	
	if ( ! $slug ) {
		return $strings['record-type-album'];
	} else {
		return ( isset( $strings[ $slug ] ) ) ? $strings[ $slug ] : '';
	}
}


/**
 * Get Record Release Year
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
 * Get Record Artist
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
 * Get Record Link
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
 * Get Record Genre
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
 * Get Tracks
 *
 * @since 1.0.0
 * @param int $post_id Post ID.
 * @return array
 */
function get_audiotheme_record_tracks( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	
	$args = array(
		'post_parent' => absint( $post_id ),
		'post_type'   => 'audiotheme_track',
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
		'numberposts' => -1
	);
	
	$tracks = get_posts( $args );

    if ( ! $tracks ) {
        $tracks = false;
    }
    
    return $tracks;
}


/**
 * Has Track Download
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string|bool File url if downloadable, else false.
 */
function is_audiotheme_track_downloadable( $post_id = null ) {
	$return = false;
	
	$is_downloadable = get_post_meta( $post_id, '_audiotheme_is_downloadable', true );
	
	if ( $is_downloadable ) {
		$file_url = get_audiotheme_track_file_url( $post_id );
		
		if ( $file_url ) {
			$return = $file_url;
		}
	}
	
	return apply_filters( 'audiotheme_track_download_url', $return, $post_id );
}


/**
 * Get Track Artist
 *
 * @since 1.0.0
 * @param int $post_id. Post ID.
 * @return string
 */
function get_audiotheme_track_artist( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
    return get_post_meta( $post_id, '_audiotheme_artist', true );
}


/**
 * Get Track File URL.
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
 * Get Track Purchase URL.
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

?>