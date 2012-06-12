<?php
/**
 * Record Link Sources
 *
 * List of default resources in which records can be purchased.
 * The options listed here show up as suggestions when the user types.
 *
 * @return array
 * @since 1.0
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
 * @since 1.0
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
 * @since 1.0
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
 * @since 1.0
 */
function get_audiotheme_record_type_string( $slug ) {
	
	$strings = get_audiotheme_record_type_slugs();
	
	if ( ! $slug ) {
		return $strings['record-type-album'];
	} else {
		return ( isset( $strings[ $slug ] ) ) ? $strings[ $slug ] : '';
	}
	
}



?>