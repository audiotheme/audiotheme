<?php
/**
 * Record's track ID's
 *
 * @since 1.0
 * @return array
 */
function audiotheme_get_tracks( $record_id ){
    $tracks = get_post_meta( $record_id, '_tracks', true );
    if( !$tracks ){
        $tracks = array();
    } else {
        $tracks = explode( ',', $tracks );
    }
    
    return $tracks;
}

/**
 * Track file
 *
 * @since 1.0
 */
function audiotheme_get_track_file( $track_id ){
   return get_post_meta( $track_id, '_track_file_url', true );
}

/**
 * Track artist
 *
 * @since 1.0
 */
function audiotheme_get_track_artist( $track_id ){
    return get_post_meta( $track_id, '_artist', true );
}

?>