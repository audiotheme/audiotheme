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
		'Amazon MP3' => array( 'icon' => '' ),
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
 * Get Record Release Year.
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return string
 */
function get_audiotheme_record_release_year( $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
	return get_post_meta( $post_id, '_release_year', true );
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
	return get_post_meta( $post_id, '_artist', true );
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
	return get_post_meta( $post_id, '_record_links', true );
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
	return get_post_meta( $post_id, '_genre', true );
}


/**
 * Get Record Custom URL
 *
 * @since 1.0.0
 * @param int $post_id. Post ID.
 * @return string
 */
function get_audiotheme_record_custom_url( $post_id ) {
   return get_post_meta( $post_id, '_url', true );
}


/**
 * Get Tracks List
 *
 * Utility function to get the tracks list and 
 * return array of track ID and Name.
 *
 * @return Array Track ID and Name
 * @since 1.0.0
 */
function get_audiotheme_tracks_list() {
	$list = array();  
	
	$args = array(
		'post_type' => 'audiotheme_track'
	);
	
	$tracks = get_posts( $args );
	
	foreach ( (array) $tracks as $track ) {
	    $list[$track->ID] = $track->post_title;
	}
	
	return $list;
}


/**
 * Get Tracks
 *
 * @since 1.0.0
 * @param int $post_id Optional. Post ID.
 * @return array
 */
function get_audiotheme_tracks( $post_id ) {
	$args = array(
		'post_parent' => absint( $post_id ),
		'post_type'   => 'audiotheme_track',
		'numberposts' => -1
	);
	
	$tracks = get_posts( $args );

    if ( ! $tracks ) {
        $tracks = false;
    }
    
    return $tracks;
}


/**
 * Has Track File URL
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
 * Has Track Download
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return bool Whether post has an video url supplied.
 */
function has_audiotheme_track_download( $post_id = null ) {
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
 * Has Track Purchase URL
 *
 * @since 1.0.0
 *
 * @param int $post_id Optional. Post ID.
 * @return bool Whether post has an video url supplied.
 */
function has_audiotheme_track_purchase_url( $post_id = null ) {
	return (bool) get_audiotheme_track_purchase_url( $post_id );
}


/**
 * Get Track Artist
 *
 * @since 1.0.0
 * @param int $post_id. Post ID.
 * @return string
 */
function get_audiotheme_track_artist( $post_id ) {
    return get_post_meta( $post_id, '_artist', true );
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
	return get_post_meta( $post_id, '_file_url', true );
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
	return get_post_meta( $post_id, '_purchase_url', true );
}

?>