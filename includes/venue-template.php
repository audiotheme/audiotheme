<?php
/**
 * Venue template tags and functions.
 *
 * @package   AudioTheme\Template
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Check if a gig has a venue.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return bool
 */
function audiotheme_gig_has_venue( $post = null ) {
	$gig = get_audiotheme_gig( $post );

	return ! empty( $gig->venue );
}

/**
 * Update a gig's venue and the gig count for any modified venues.
 *
 * @since 1.0.0
 *
 * @param int    $gig_id Gig post ID.
 * @param string $venue_name Venue name.
 * @return WP_Post|false Venue post or false.
 */
function set_audiotheme_gig_venue( $gig_id, $venue_name ) {
	$gig = get_audiotheme_gig( $gig_id ); // Retrieve current venue info.
	$venue_name = trim( wp_unslash( $venue_name ) );

	if ( empty( $venue_name ) ) {
		set_audiotheme_gig_venue_id( $gig_id, 0 );
	} elseif ( ! isset( $gig->venue->name ) || $venue_name !== $gig->venue->name ) {
		$new_venue = get_audiotheme_venue_by( 'name', $venue_name );

		if ( ! $new_venue ) {
			$new_venue = array(
				'name'      => $venue_name,
				'gig_count' => 1,
			);

			// Time zone is important, so retrieve it from the global $_POST array if it exists.
			if ( ! empty( $_POST['audiotheme_venue']['timezone_string'] ) ) {
				$new_venue['timezone_string'] = $_POST['audiotheme_venue']['timezone_string'];
			}

			$venue_id = save_audiotheme_venue( $new_venue );
			if ( $venue_id ) {
				set_audiotheme_gig_venue_id( $gig_id, $venue_id );
			} else {
				set_audiotheme_gig_venue_id( $gig_id, 0 );
			}
		} else {
			$venue_id = $new_venue->ID;
			set_audiotheme_gig_venue_id( $gig_id, $venue_id );
		}
	}

	$venue = false;
	if ( ! empty( $venue_id ) ) {
		$venue = get_audiotheme_venue( $venue_id );
	}

	return $venue;
}

/**
 * Update a gig's venue and the gig count for any modified venues.
 *
 * @since 2.0.0
 *
 * @param int $gig_id Gig ID.
 * @param int $venue_id Venue ID.
 * @return object Venue object.
 */
function set_audiotheme_gig_venue_id( $gig_id, $venue_id ) {
	global $wpdb;

	$gig_id   = absint( $gig_id );
	$venue_id = absint( $venue_id );

	// Query for existing connections.
	$old_venue_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT p2p_from
		FROM $wpdb->p2p
		WHERE p2p_type = 'audiotheme_venue_to_gig' AND p2p_to = %d",
		$gig_id
	) );

	// Remove the existing connection.
	if ( $venue_id !== absint( $old_venue_id ) ) {
		p2p_delete_connections(
			'audiotheme_venue_to_gig',
			array( 'to' => $gig_id )
		);

		update_audiotheme_venue_gig_count( $old_venue_id );
	}

	// Bail if the there isn't a new venue ID.
	if ( empty( $venue_id ) ) {
		delete_post_meta( $gig_id, '_audiotheme_venue_id' );
		delete_post_meta( $gig_id, '_audiotheme_venue_guid' );
		return null;
	}

	if ( $venue_id !== absint( $old_venue_id ) ) {
		p2p_create_connection(
			'audiotheme_venue_to_gig',
			array(
				'from' => $venue_id,
				'to'   => $gig_id,
			)
		);
	}

	update_audiotheme_venue_gig_count( $venue_id );

	update_post_meta( $gig_id, '_audiotheme_venue_id', $venue_id );

	return get_audiotheme_venue( $venue_id );
}

/**
 * Retrieve a venue by its ID.
 *
 * @since 1.0.0
 *
 * @param int|WP_Post $post Post ID or object.
 * @return WP_Post
 */
function get_audiotheme_venue( $post = null ) {
	if ( null === $post ) {
		$gig  = get_audiotheme_gig();
		$post = get_post( $gig->venue->ID );
	} else {
		$post = get_post( $post );
	}

	if ( empty( $post ) ) {
		return null;
	}

	$defaults = get_default_audiotheme_venue_properties();
	$meta = (array) get_post_custom( $post->ID );
	foreach ( $meta as $key => $val ) {
		$meta[ str_replace( '_audiotheme_', '', $key ) ] = $val;
		unset( $meta[ $key ] );
	}

	$properties = wp_parse_args( $meta, $defaults );

	foreach ( $properties as $key => $prop ) {
		if ( ! array_key_exists( $key, $defaults ) ) {
			unset( $properties[ $key ] );
		} elseif ( isset( $prop[0] ) ) {
			$properties[ $key ] = maybe_unserialize( $prop[0] );
		}
	}

	$venue['ID'] = $post->ID;
	$venue['name'] = $post->post_title;
	$venue = (object) wp_parse_args( $venue, $properties );

	return $venue;
}

/**
 * Retrieve a venue by a property.
 *
 * The only field currently supported is the venue name.
 *
 * @since 1.0.0
 *
 * @param string $field Field name.
 * @param string $value Field value.
 * @return WP_Post|false
 */
function get_audiotheme_venue_by( $field, $value ) {
	global $wpdb;

	$field = 'name';

	$venue_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title=%s", $value ) );
	if ( ! $venue_id ) {
		return false;
	}

	$venue = get_audiotheme_venue( $venue_id );

	return $venue;
}

/**
 * Get the default venue object properties.
 *
 * Useful for whitelisting data in other API methods.
 *
 * @since 1.0.0
 */
function get_default_audiotheme_venue_properties() {
	$args = array(
		'ID'              => 0,
		'name'            => '',
		'address'         => '',
		'city'            => '',
		'state'           => '',
		'postal_code'     => '',
		'country'         => '',
		'website'         => '',
		'phone'           => '',
		'contact_name'    => '',
		'contact_phone'   => '',
		'contact_email'   => '',
		'notes'           => '',
		'timezone_string' => '',
	);

	return $args;
}

/**
 * Prepare a venue for use in JavaScript.
 *
 * @since 2.0.0
 *
 * @param int $venue_id Venue ID.
 * @return object Venue object.
 */
function prepare_audiotheme_venue_for_js( $venue_id ) {
	if ( empty( $venue_id ) ) {
		$post = get_default_audiotheme_venue_properties();
	} else {
		$post = (array) get_audiotheme_venue( $venue_id );
	}

	$post['nonces']['update'] = false;
	if ( current_user_can( 'edit_post', $post['ID'] ) ) {
		$post['nonces']['update'] = wp_create_nonce( 'update-post_' . $post['ID'] );
	}

	return (object) $post;
}

/**
 * Display or retrieve the link to the current venue's website.
 *
 * @since 1.0.0
 *
 * @param array $args Optional. Passed to get_audiotheme_venue_link().
 * @param bool  $echo Optional. Default to true. Whether to display or return.
 * @return string|null Null on failure or display. String when echo is false.
 */
function the_audiotheme_gig_venue_link( $args = array(), $echo = true ) {
	$gig = get_audiotheme_gig();

	if ( empty( $gig->venue ) ) {
		return;
	}

	$html = get_audiotheme_venue_link( $gig->venue->ID, $args );

	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Retrieve the link to a venue's website.
 *
 * The args are:
 * 'before' - Default is '' (string). The html or text to prepend to the link.
 * 'after' - Default is '' (string). The html or text to append to the link.
 * 'before_link' - Default is '<span class="summary" itemprop="name">' (string).
 *      The html or text to prepend to each link inside the <a> tag.
 * 'after_link' - Default is '</span>' (string). The html or text to append to each
 *      link inside the <a> tag.
 *
 * @since 1.0.0
 *
 * @param int   $venue_id Venue post ID.
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_link( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	if ( empty( $venue->name ) ) {
		return '';
	}

	$before_link = '<span class="fn org" itemprop="name">';

	$args = wp_parse_args( $args, array(
		'before'      => '',
		'after'       => '',
		'before_link' => $before_link,
		'after_link'  => '</span>',
		'microdata'   => false,
	) );

	$schema = $args['microdata'] ? ' itemprop="url"' : '';

	// Remove microdata. This is for backward compatibility.
	if ( ! $args['microdata'] && $args['before_link'] === $before_link ) {
		$args['before_link'] = '<span class="fn org">';
	}

	$html  = $args['before'];
	$html .= empty( $venue->website ) ? '' : sprintf( '<a href="%s" class="url"' . $schema . '>', esc_url( $venue->website ) );
	$html .= $args['before_link'] . $venue->name . $args['after_link'];
	$html .= empty( $venue->website ) ? '' : '</a>';
	$html .= $args['after'];

	return $html;
}

/**
 * Display or retrieve the current venue in vCard markup.
 *
 * @since 1.0.0
 *
 * @param array $args Optional. Passed to get_audiotheme_venue_vcard().
 * @param bool  $echo Optional. Default to true. Whether to display or return.
 * @return string|null Null on failure or display. String when echo is false.
 */
function the_audiotheme_venue_vcard( $args = array(), $echo = true ) {
	$gig = get_audiotheme_gig();

	if ( empty( $gig->venue ) ) {
		return;
	}

	$html = get_audiotheme_venue_vcard( $gig->venue->ID, $args );

	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * Retrieve a venue with vCard markup.
 *
 * The defaults for overwriting are:
 * 'container' - Default is 'dd' (string). The html or text to wrap the vCard.
 *
 * @since 1.0.0
 *
 * @param int   $venue_id Venue post ID.
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_vcard( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	$args = wp_parse_args( $args, array(
		'container'      => 'dd',
		'microdata'      => true,
		'show_country'   => true,
		'show_name'      => true,
		'show_name_link' => true,
		'show_phone'     => true,
	) );

	$formatter = new AudioTheme_AddressFormatter( $venue );
	$output = $formatter->get_html( $args );

	if ( ! empty( $output ) && ! empty( $args['container'] ) ) {
		$output = sprintf(
			'<%1$s class="location vcard"%2$s%3$s%4$s>%5$s</%1$s>',
			$args['container'],
			$args['microdata'] ? ' itemprop="location"' : '',
			$args['microdata'] ? ' itemscope' : '',
			$args['microdata'] ? ' itemtype="http://schema.org/EventVenue"' : '',
			$output
		);
	}

	return $output;
}

/**
 * Retrieve a venue's address as a string.
 *
 * @since 1.0.0
 *
 * @param int   $venue_id Venue post ID.
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_address( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	$args = wp_parse_args( $args, array(
		'separator'    => ', ',
		'show_country' => false,
		'show_name'    => false,
		'show_phone'   => false,
	) );

	$formatter = new AudioTheme_AddressFormatter( $venue );

	return $formatter->get_text( $args );
}

/**
 * Retrieve a venue's location (city, region, country).
 *
 * @since 1.1.0
 *
 * @param int   $venue_id Venue post ID.
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_location( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	$location  = '';
	$location .= empty( $venue->city ) ? '' : '<span class="locality">' . $venue->city . '</span>';
	$location .= empty( $location ) || empty( $venue->state ) ? '' : '<span class="sep sep-region">,</span> ';
	$location .= empty( $venue->state ) ? '' : '<span class="region">' . $venue->state . '</span>';

	if ( ! empty( $venue->country ) && apply_filters( 'show_audiotheme_venue_country', true ) ) {
		$country_class = esc_attr( 'country-name-' . sanitize_title_with_dashes( $venue->country ) );

		$location .= empty( $location ) ? '': '<span class="sep sep-country-name ' . $country_class . '">,</span> ';
		$location .= empty( $venue->country ) ? '' : '<span class="country-name ' . $country_class . '">' . $venue->country . '</span>';
	}

	return $location;
}

/**
 * Retrieve a unique venue name.
 *
 * @since 1.0.0
 *
 * @param string $name Venue name.
 * @param int    $venue_id Venue post ID.
 * @return string
 */
function get_unique_audiotheme_venue_name( $name, $venue_id = 0 ) {
	global $wpdb;

	$suffix = 2;
	while ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'audiotheme_venue' AND ID != %d", $name, $venue_id ) ) ) {
		$name .= ' ' . $suffix;
	}

	return $name;
}

/**
 * Save a venue.
 *
 * Accepts an array of properties, whitelists them and then saves. Will update values if the ID isn't 0.
 * Sets all post meta fields upon initial save, even if empty.
 *
 * @since 1.0.0
 *
 * @param array $data Venue data.
 * @return int|false Venue post ID or false.
 */
function save_audiotheme_venue( $data ) {
	global $wpdb;

	$action       = 'update';
	$current_user = wp_get_current_user();
	$defaults     = get_default_audiotheme_venue_properties();

	// New venue.
	if ( empty( $data['ID'] ) ) {
		$action = 'insert';
		$data   = wp_parse_args( $data, $defaults );
	} else {
		$current_venue = get_audiotheme_venue( $data['ID'] );
	}

	// Copy gig count before cleaning the data array.
	$gig_count = isset( $data['gig_count'] ) && is_numeric( $data['gig_count'] ) ? absint( $data['gig_count'] ) : 0;

	// Remove properties that aren't whitelisted.
	$data = array_intersect_key( $data, $defaults );

	// Map the 'name' property to the 'post_title' field.
	if ( ! empty( $data['name'] ) ) {
		$post_title = $data['name'];

		if ( ! isset( $current_venue ) || $post_title !== $current_venue->name ) {
			$venue['post_title'] = $post_title;
			$venue['post_name'] = '';
		}
	}

	// Insert the post container.
	if ( 'insert' === $action ) {
		$venue['post_author'] = $current_user->ID;
		$venue['post_status'] = 'publish';
		$venue['post_type'] = 'audiotheme_venue';

		$venue_id = wp_insert_post( $venue );
	} else {
		$venue_id = absint( $data['ID'] );
		$venue['ID'] = $venue_id;
		wp_update_post( $venue );
	}

	// Set the venue title as the venue ID if the name argument was empty.
	if ( isset( $data['name'] ) && empty( $data['name'] ) ) {
		wp_update_post( array(
			'ID'         => $venue_id,
			'post_title' => get_unique_audiotheme_venue_name( $venue_id, $venue_id ),
			'post_name'  => '',
		) );
	}

	// Save additional properties to post meta.
	if ( $venue_id ) {
		unset( $data['ID'] );
		unset( $data['name'] );

		foreach ( $data as $key => $value ) {
			$meta_key = '_audiotheme_' . $key;

			if ( 'website' === $key ) {
				$value = esc_url_raw( $value );
			} else {
				$value = sanitize_text_field( $value );
			}

			update_post_meta( $venue_id, $meta_key, $value );
		}

		// Update gig count.
		update_audiotheme_venue_gig_count( $venue_id, $gig_count );

		return $venue_id;
	}

	return false;
}

/**
 * Update the number of gigs at a particular venue.
 *
 * @since 1.0.0
 *
 * @param int $venue_id Venue post ID.
 */
function get_audiotheme_venue_gig_count( $venue_id ) {
	global $wpdb;

	$sql = $wpdb->prepare( "SELECT count( * )
		FROM $wpdb->p2p
		WHERE p2p_type='audiotheme_venue_to_gig' AND p2p_from=%d",
	$venue_id );
	$count = $wpdb->get_var( $sql );

	return ( empty( $count ) ) ? 0 : $count;
}

/**
 * Update the number of gigs at a particular venue.
 *
 * @since 1.0.0
 *
 * @param int $venue_id Venue post ID.
 * @param int $count Optional. Number of gigs assigned to a venue.
 */
function update_audiotheme_venue_gig_count( $venue_id, $count = 0 ) {
	global $wpdb;

	if ( ! $count ) {
		$count = get_audiotheme_venue_gig_count( $venue_id );
	}

	update_post_meta( $venue_id, '_audiotheme_gig_count', absint( $count ) );
}

/**
 * Build a URL to a Google Map.
 *
 * @since 1.6.0
 *
 * @param array $args Array of args.
 * @param int   $venue_id Optional. Venue ID.
 * @return string
 */
function get_audiotheme_google_map_url( $args = array(), $venue_id = 0 ) {
	$args = wp_parse_args( $args, array(
		'address' => '',
	) );

	// Get the current post and determine if it's a gig with a venue.
	if ( empty( $args['address'] ) && ( $gig = get_audiotheme_gig() ) ) {
		if ( 'audiotheme_gig' === get_post_type( $gig ) && ! empty( $gig->venue->ID ) ) {
			$venue_id = $gig->venue->ID;
		}
	}

	// Retrieve the address for the venue.
	if ( $venue_id ) {
		$venue = get_audiotheme_venue( $venue_id );

		$args['address'] = get_audiotheme_venue_address( $venue->ID );
		$args['address'] = $args['address'] ? $args['address'] : $venue->name;
	}

	$url = add_query_arg( array(
		'q' => rawurlencode( $args['address'] ),
	), 'https://maps.google.com/maps' );

	return apply_filters( 'audiotheme_google_map_url', $url, $args, $venue_id );
}

/**
 * Generate a Google Map iframe for an address or venue.
 *
 * If a venue ID is passed as the second parameter, it's address will supercede
 * the address argument in the $args array.
 *
 * If the address argument is left empty and the current post is a gig CPT and
 * it has a venue with an address, that is the address that will be used.
 *
 * The args are:
 * 'address' - Default is '' (string). The address to send to Google.
 * 'width' - Default is '100%' (string). Width of the iframe.
 * 'height' - Default is 300 (string). Height of the iframe.
 *
 * @since 1.2.0
 *
 * @param array $args Array of args.
 * @param int   $venue_id Optional. Venue ID.
 * @return string
 */
function get_audiotheme_google_map_embed( $args = array(), $venue_id = 0 ) {
	$args = wp_parse_args( $args, array(
		'address'   => '',
		'latitude'  => '',
		'longitude' => '',
		'width'     => '100%',
		'height'    => 300,
		'link_text' => esc_html__( 'Get Directions', 'audiotheme' ),
		'format'    => '%1$s<p class="venue-map-link">%2$s</p>',
	) );

	// Get the current post and determine if it's a gig with a venue.
	if ( empty( $args['address'] ) && ( $gig = get_audiotheme_gig() ) ) {
		if ( 'audiotheme_gig' === get_post_type( $gig ) && ! empty( $gig->venue->ID ) ) {
			$venue_id = $gig->venue->ID;
		}
	}

	// Retrieve the address for the venue.
	if ( $venue_id ) {
		$venue = get_audiotheme_venue( $venue_id );

		$address = get_audiotheme_venue_address( $venue->ID );
		$args['address'] = $address ? $venue->name . ', ' . $address : $venue->name;

		$args['latitude']  = get_post_meta( $venue_id, '_audiotheme_latitude', true );
		$args['longitude'] = get_post_meta( $venue_id, '_audiotheme_longitude', true );

		// Remove the venue name from the query if coordinates are available.
		if ( ! empty( $args['latitude'] ) && ! empty( $args['longitude'] ) && ! empty( $address ) ) {
			$args['address'] = $address;
		}
	}

	$args['embed_url'] = add_query_arg( array(
		'q'      => rawurlencode( $args['address'] ),
		'output' => 'embed',
		'key'    => audiotheme()->modules['gigs']->get_google_maps_api_key(),
	), 'https://maps.google.com/maps' );

	// Add coordinates if they exist.
	if ( ! empty( $args['latitude'] ) && ! empty( $args['longitude'] ) ) {
		$args['embed_url'] = add_query_arg( array(
			'll' => $args['latitude'] . ',' . $args['longitude'],
			'z'  => 15,
		), $args['embed_url'] );
	}

	$args['link_url'] = add_query_arg( 'q', urlencode( $args['address'] ), 'https://maps.google.com/maps' );

	$args = apply_filters( 'audiotheme_google_map_embed_args', $args, $venue_id );

	$iframe = sprintf( '<iframe src="%s" width="%s" height="%s" frameBorder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>',
		esc_url( $args['embed_url'] ),
		esc_attr( $args['width'] ),
		esc_attr( $args['height'] )
	);

	$link = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $args['link_url'] ), $args['link_text'] );

	$output = sprintf( $args['format'], $iframe, $link );

	return apply_filters( 'audiotheme_google_map_embed', $output, $args, $venue_id );
}

/**
 * Retrieve the static Google map URL for an address/venue.
 *
 * @since 1.6.0
 *
 * @link https://developers.google.com/maps/documentation/staticmaps/?csw=1
 *
 * @param array $args Array of args.
 * @param int   $venue_id Optional. Venue ID.
 * @return string
 */
function get_audiotheme_google_static_map_url( $args = array(), $venue_id = 0 ) {
	$args = wp_parse_args( $args, array(
		'address'   => '',
		'latitude'  => '',
		'longitude' => '',
		'width'     => 640,
		'height'    => 300,
	) );

	// Get the current post and determine if it's a gig with a venue.
	if ( empty( $args['address'] ) && ( $gig = get_audiotheme_gig() ) ) {
		if ( 'audiotheme_gig' === get_post_type( $gig ) && ! empty( $gig->venue->ID ) ) {
			$venue_id = $gig->venue->ID;
		}
	}

	// Retrieve the address for the venue.
	if ( $venue_id ) {
		$venue = get_audiotheme_venue( $venue_id );

		$args['address'] = get_audiotheme_venue_address( $venue->ID );
		$args['address'] = ( $args['address'] ) ? $venue->name . ', ' . $args['address'] : $venue->name;

		$args['latitude']  = get_post_meta( $venue_id, '_audiotheme_latitude', true );
		$args['longitude'] = get_post_meta( $venue_id, '_audiotheme_longitude', true );
	}

	$image_url = add_query_arg(
		array(
			'center'  => rawurlencode( $args['address'] ),
			'size'    => $args['width'] . 'x' . $args['height'],
			'scale'   => 2,
			'format'  => 'jpg',
			'sensor'  => 'false',
			'markers' => 'size:small|color:0xff0000|' . rawurlencode( $args['address'] ),
			'key'     => audiotheme()->modules['gigs']->get_google_maps_api_key(),
		),
		'https://maps.googleapis.com/maps/api/staticmap'
	);

	// Use coordinates if they exist.
	if ( ! empty( $args['latitude'] ) && ! empty( $args['longitude'] ) ) {
		$image_url = add_query_arg(
			array(
				'center'  => $args['latitude'] . ',' . $args['longitude'],
				'markers' => 'size:small|color:0xff0000|' . rawurlencode( $args['latitude'] . ',' . $args['longitude'] ),
				'zoom'    => 14,
			),
			$image_url
		);
	}

	$image_url = apply_filters( 'audiotheme_google_static_map_url', $image_url, $args, $venue_id );

	// @link https://developers.google.com/maps/documentation/staticmaps/?csw=1#StyledMaps
	$map_styles = apply_filters( 'audiotheme_google_static_map_styles', array() );
	if ( ! empty( $map_styles ) ) {
		foreach ( $map_styles as $styles ) {
			$image_url .= '&style=' . audiotheme_build_query( $styles );
		}
	}

	return $image_url;
}
