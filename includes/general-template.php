<?php
/**
 * AudioTheme Theme Option
 *
 * Function called to get a Theme Option. 
 * The option defaults to false unless otherwise set.
 *
 * @since 1.0.0
 */
function get_audiotheme_theme_option( $key, $default = false, $option_name = '' ) {
	$option_name = ( empty( $option_name ) ) ? 'audiotheme_options' : $option_name;
	
	$options = get_option( $option_name );
	
	return ( isset( $options[ $key ] ) ) ? $options[ $key ] : $default;
}

/**
 * Get Category List
 *
 * Utility function to get the category list and 
 * return array of category ID and Name.
 *
 * @return Array Category ID and Name
 * @since 1.0.0
 */
function get_audiotheme_category_list() {
	// Pull all the categories into an array
	$list = array();  
	$categories = get_categories();
	$list[''] = __( 'Select a category:', 'audiotheme-i18n' );
	
	foreach ( (array) $categories as $category )
	    $list[$category->cat_ID] = $category->cat_name;
	
	return $list;
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
	// Pull all the tracks into an array
	$list = array();  
	$tracks = get_posts( array( 'post_type' => 'audiotheme_track' ) );
	
	foreach ( (array) $tracks as $track )
	    $list[$track->ID] = $track->post_title;
	
	return $list;
}

/**
 * Record's track ID's
 *
 * @since 1.0.0
 * @return array
 */
function get_audiotheme_tracks( $record_id ){
	$args=array(
	  'post_parent' => $record_id,
	  'post_type' => 'audiotheme_track',
	  'numberposts' => -1
	);
	$tracks = get_posts($args);

    if( !$tracks ){
        $tracks = false;
    }
    
    return $tracks;
}

/**
 * Track file
 *
 * @since 1.0.0
 */
function get_audiotheme_record_custom_url( $record_id ){
   return get_post_meta( $record_id, '_url', true );
}

/**
 * Track file
 *
 * @since 1.0.0
 */
function get_audiotheme_track_file( $track_id ){
   return get_post_meta( $track_id, '_file_url', true );
}

/**
 * Track artist
 *
 * @since 1.0.0
 */
function get_audiotheme_track_artist( $track_id ){
    return get_post_meta( $track_id, '_artist', true );
}

?>