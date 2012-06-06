<?php
function get_audiotheme_record_link_sources() {
	$default_sources = array(
		'Amazon MP3' => array( 'icon' => '' ),
		'CD Baby' => array( 'icon' => '' ),
		'iTunes' => array( 'icon' => '' )
	);
	
	return apply_filters( 'audiotheme_record_link_sources', $default_sources );
}

function get_audiotheme_record_type_strings() {
	$strings = array(
		'record-type-album'  => _x( 'Album',  'Record type', 'audiotheme-i18n' ),
		'record-type-single' => _x( 'Single', 'Record type', 'audiotheme-i18n' )
	);
	return $strings;
}

function get_audiotheme_record_type_slugs() {
	$slugs = array_keys( get_audiotheme_record_type_strings() );
	return array_combine( $slugs, $slugs );
}

function get_audiotheme_record_type_string( $slug ) {
	$strings = get_audiotheme_record_type_slugs();
	if ( ! $slug ) {
		return $strings['record-type-album'];
	} else {
		return ( isset( $strings[ $slug ] ) ) ? $strings[ $slug ] : '';
	}
}

function audiotheme_track_has_download( $post_id ) {
	$return = false;
	
	$allow_download = get_post_meta( $post_id, '_allow_download', true );
	if ( $allow_download ) {
		$file_url = get_post_meta( $post_id, '_file_url', true );
		if ( $file_url && false === strpos( $file_url, 'spotify:' ) ) {
			$return = $file_url;
		}
	}
	
	return apply_filters( 'audiotheme_track_download_url', $return, $post_id );
}
?>