<?php
function get_audiotheme_gig_rss_description( $post = null ) {
	$gig = get_audiotheme_gig( $post );
	
	$output = '<strong>' . get_audiotheme_gig_time( 'l, F j, Y', ' @ g:i a' ) . '</strong>';
	$output.= ( empty( $gig->venue ) ) ? '' : get_audiotheme_venue_vcard( $gig->venue->ID, array( 'container' => 'div' ) );
	$output.= ( empty( $gig->post_excerpt ) ) ? '' : wpautop( $gig->post_excerpt );
	
	return $output;
}


function get_audiotheme_venue_vcard_rss( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );
	
	$output = '';
	
	$website = ( empty( $venue->website ) ) ? '' : ' rdf:about="' . esc_url( $venue->website ) . '"';
	$output.= sprintf( '<v:vCard%s">', $website );
		$output.= '<v:fn>' . esc_html( $venue->name ) . '</v:fn>';
		
		$address = '';
		$address.= ( empty( $venue->address ) ) ? '' : '<v:street-address>' . esc_html( $venue->address ) . '</v:street-address>';
		
		$locality = ( empty( $venue->city ) ) ? '' : $venue->city;
		$locality.= ( ! empty( $locality ) && ! empty( $venue->state ) ) ? ', ' : '';
		$locality.= ( empty( $venue->state ) ) ? '' : $venue->state;
		$address.= ( empty( $locality ) ) ? '' : '<v:locality>' . esc_html( $locality ) . '</v:locality>, ';
		
		$address.= ( empty( $venue->postal_code ) ) ? '' : '<v:postal-code>' . esc_html( $venue->postal_code ) . '</v:postal-code>';
		$address.= ( empty( $venue->country ) ) ? '' : '<v:country-name>' . $venue->country . '</v:country-name>';
		
		if( ! empty( $address ) ) {
			$output.= '<v:adr><rdf:Description>' . $address . '</rdf:Description></v:adr>';
		}
		
		$output.= ( empty( $venue->phone ) ) ? '' : '<v:tel><rdf:Description><rdf:value>' . $venue->phone . '</rdf:value></rdf:Description></v:tel>';
	$output.= '</v:VCard>';
	
	return $output;
}


function get_audiotheme_venue_location_ical( $venue_id ) {
	$venue = get_audiotheme_venue( $venue_id );
	
	$output = $venue->name;
	
	$address = array();
	if ( ! empty( $venue->address ) ) {
		$address[] = $venue->address;
	}
	
	$locality = ( empty( $venue->city ) ) ? '' : $venue->city;
	$locality.= ( ! empty( $locality ) && ! empty( $venue->state ) ) ? ', ' : '';
	$locality.= ( empty( $venue->state ) ) ? '' : $venue->state;
	if ( ! empty( $locality ) ) {
		$address[] = $locality;
	}
	
	if ( ! empty( $venue->country ) ) {
		$address[] = $venue->country;
	}
	
	if ( ! empty( $venue->postal_code ) ) {
		$address[] = $venue->postal_code;
	}
	
	if ( ! empty( $address ) ) {
		$output.= ', ' . join( $address, ', ' );
	}
	
	return escape_ical_text( $output );
}


if ( ! function_exists( 'escape_ical_text' ) ) :
function escape_ical_text( $text ) {
	$search = array( '\\', ';', ',', "\n", "\r" );
	$replace = array( '\\\\', '\;', '\,', ' ', ' ' );
	
	return str_replace( $search, $replace, $text );
}
endif;
?>