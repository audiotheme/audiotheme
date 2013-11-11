<?php
/**
 * Gig and venue template functions.
 *
 * @package AudioTheme_Framework
 * @subpackage Template
 */

/**
 * Retrieve a gig object with associated venue.
 *
 * If the $post parameter is omitted get_post() defaults to the current
 * post in the WordPress Loop.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return object Post with additional gig info.
 */
function get_audiotheme_gig( $post = null ) {
	$post = get_post( $post );
	$gig_id = $post->ID;

	$post->gig_datetime = get_post_meta( $gig_id, '_audiotheme_gig_datetime', true );
	$post->gig_time = '';
	$post->tickets_price = get_post_meta( $gig_id, '_audiotheme_tickets_price', true );
	$post->tickets_url = get_post_meta( $gig_id, '_audiotheme_tickets_url', true );

	// determine the gig time
	$gig_time = get_post_meta( $post->ID, '_audiotheme_gig_time', true );
	$t = date_parse( $gig_time );
	if ( empty( $t['errors'] ) ) {
		$post->gig_time = mysql2date( get_option( 'time_format' ), $post->gig_datetime );
	}

	$post->venue = null;
	if ( isset( $post->connected[0] ) && isset( $post->connected[0]->ID ) ) {
		$post->venue = get_audiotheme_venue( $post->connected[0]->ID );
	} elseif ( ! isset( $post->connected ) ) {
		$venues = get_posts( array(
			'post_type'        => 'audiotheme_venue',
			'connected_type'   => 'audiotheme_venue_to_gig',
			'connected_items'  => $post->ID,
			'nopaging'         => true,
			'suppress_filters' => false,
		) );

		if ( ! empty( $venues ) ) {
			$post->venue = get_audiotheme_venue( $venues[0]->ID );
		}
	}

	return $post;
}

/**
 * Retrieve a gig's title.
 *
 * If the title is empty, attempt to construct one from the venue name
 * or fallback to the gig date.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return object Gig title.
 */
function get_audiotheme_gig_title( $post = null ) {
	$gig = get_audiotheme_gig( $post );

	$title = ( empty( $gig->post_title ) ) ? '' : $gig->post_title;

	if ( empty( $title ) ) {
		if ( ! empty( $gig->venue->name ) ) {
			$title = $gig->venue->name;
		} else {
			$title = get_audiotheme_gig_time( 'F j, Y' );
		}
	}

	return apply_filters( 'get_audiotheme_gig_title', $title, $gig );
}

/**
 * Display or retrieve the link to the current gig.
 *
 * @since 1.0.0
 *
 * @param array $args Optional. Passed to get_audiotheme_gig_link()
 * @param bool $echo Optional. Default to true. Whether to display or return.
 * @return string|null Null on failure or display. String when echo is false.
 */
function the_audiotheme_gig_link( $args = array(), $echo = true ) {
	$html = get_audiotheme_gig_link( null, $args );

	if ( $echo )
		echo $html;
	else
		return $html;
}

/**
 * Retrieve the link to the current gig.
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
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_gig_link( $post = null, $args = array() ) {
	$gig = get_audiotheme_gig( $post );

	$defaults = array(
		'before'      => '',
		'after'       => '',
		'before_link' => '<span class="summary" itemprop="name">',
		'after_link'  => '</span>',
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$html  = $before;
	$html .= '<a href="' . esc_url( get_permalink( $gig->ID ) ) . '" class="url uid" itemprop="url">';
	$html .= $before_link . get_audiotheme_gig_title( $post ) . $after_link;
	$html .= '</a>';
	$html .= $after;

	return $html;
}

/**
 * Retrieve a gig's date/time in GMT.
 *
 * If the time hasn't been saved for a gig, will return date only.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string MySQL date or datetime.
 */
function get_audiotheme_gig_gmt_date( $post = null ) {
	$gig = get_audiotheme_gig( $post );
	$format = 'Y-m-d H:i:s';

	$tz = get_option( 'timezone_string' );
	if ( ! empty( $gig->venue->timezone_string ) ) {
		$tz = $gig->venue->timezone_string;
	}

	$string_gmt = $gig->gig_datetime;
	if ( $tz && ! empty( $gig->gig_time ) ) {
		date_default_timezone_set( $tz );
		$datetime = new DateTime( $gig->gig_datetime );
		$datetime->setTimezone( new DateTimeZone( 'UTC' ) );
		$offset = $datetime->getOffset();
		$datetime->modify( '+' . $offset / 3600 . ' hours' );
		$string_gmt = gmdate( $format, $datetime->format( 'U' ) );
		date_default_timezone_set( 'UTC' );
	} else {
		$string_gmt = mysql2date( 'Y-m-d', $gig->gig_datetime ); // only returns the date portion since the time portion is unknown
	}

	return $string_gmt;
}

/**
 * Display a gig's date and time.
 *
 * @since 1.3.0
 * @uses get_audiotheme_gig_time()
 *
 * @param string $d Optional. PHP date format.
 * @param string $t Optional. PHP time format.
 * @param bool $gmt Optional, default is false. Whether to return the gmt time.
 * @param array $args Optional. Override the defaults.
 * @param int|object $post Optional post ID or object. Default is global $post object.
 */
function the_audiotheme_gig_time( $d = 'c', $t = '', $gmt = false, $args = null, $post = null ) {
	echo get_audiotheme_gig_time( $d, $t, $gmt, $args, $post );
}

/**
 * Retrieve a gig's date and time.
 *
 * Separates date and time parameters due to the time not always
 * being present for a gig.
 *
 * The args are:
 * 'empty_time' - Default is '' (string). The text to display if the time doesn't exist.
 * 'translate' - Default is 'true' (bool). Whether to translate the time string.
 *
 * @since 1.0.0
 *
 * @param string $d Optional. PHP date format.
 * @param string $t Optional. PHP time format.
 * @param bool $gmt Optional, default is false. Whether to return the gmt time.
 * @param array $args Optional. Override the defaults.
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string
 */
function get_audiotheme_gig_time( $d = 'c', $t = '', $gmt = false, $args = null, $post = null ) {
	$args = wp_parse_args( $args, array(
		'empty_time' => '', // displays if time hasn't been saved
		'translate'  => true,
	) );
	extract( $args, EXTR_SKIP );

	$gig = get_audiotheme_gig( $post );

	if ( empty( $gig->gig_time ) ) {
		// ISO 8601 without time component or timezone component.
		// @todo Need to verify Google Calendar support.
		$d = ( 'c' == $d ) ? 'Y-m-d' : $d;
		$format = $d;
	} else {
		$format = ( empty( $t ) ) ? $d : $d . $t;
	}

	if ( $gmt ) {
		$time = get_audiotheme_gig_gmt_date( $post );
	} else {
		$time = $gig->gig_datetime;
		$tz = get_option( 'timezone_string' );
		if ( ! empty( $gig->venue->timezone_string ) ) {
			$tz = $gig->venue->timezone_string;
		}
		date_default_timezone_set( $tz );
	}

	$time = mysql2date( $format, $time, $translate );
	$time = ( empty( $gig->gig_time ) && ! empty( $empty_time ) ) ? $time . $empty_time : $time;
	date_default_timezone_set( 'UTC' );

	return $time;
}

/**
 * Display or retrieve the current gig's description.
 *
 * @since 1.0.0
 *
 * @param string $before Optional. Content to prepend to the description.
 * @param string $after Optional. Content to append to the description.
 * @param bool $echo Optional, default to true. Whether to display or return.
 * @return null|string Null on no description. String if $echo parameter is false.
 */
function the_audiotheme_gig_description( $before = '', $after = '', $echo = true ) {
	$description = get_audiotheme_gig_description();

	$html = ( empty( $description ) ) ? '' : $before . wpautop( $description ) . $after;

	if ( $echo )
		echo $html;
	else
		return $html;
}

/**
 * Retrieve a gig's location (city, state, country).
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string Location with microformat markup.
 */
function get_audiotheme_gig_location( $post = null ) {
	$gig = get_audiotheme_gig( $post );

	$location = '';
	if ( audiotheme_gig_has_venue( $gig ) ) {
		$location = get_audiotheme_venue_location( $gig->venue->ID );
	}

	return $location;
}

/**
 * Retrieve a gig's description.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string
 */
function get_audiotheme_gig_description( $post = 0 ) {
	$gig = get_audiotheme_gig( $post );

	return $gig->post_excerpt;
}

/**
 * Does a gig have ticket meta?
 *
 * @since 1.1.0
 *
 * @param string $key Check for a particular type of meta. Defaults to any.
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return bool
 */
function audiotheme_gig_has_ticket_meta( $key = '', $post = 0 ) {
	$gig = get_audiotheme_gig( $post );

	$keys = array(
		'price' => '_audiotheme_tickets_price',
		'url'   => '_audiotheme_tickets_url',
	);

	if ( $key && ! isset( $keys[ $key ] ) ) {
		return false;
	} elseif ( $key ) {
		// Reset the keys array with a single value.
		$keys = array( $key => $keys[ $key ] );
	}

	foreach ( $keys as $key ) {
		if ( get_post_meta( $gig->ID, $key, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Retrieve a gig's ticket price.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string
 */
function get_audiotheme_gig_tickets_price( $post = 0 ) {
	$gig = get_audiotheme_gig( $post );

	return get_post_meta( $gig->ID, '_audiotheme_tickets_price', true );
}

/**
 * Retrieve a gig's ticket url.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string
 */
function get_audiotheme_gig_tickets_url( $post = 0 ) {
	$gig = get_audiotheme_gig( $post );

	return get_post_meta( $gig->ID, '_audiotheme_tickets_url', true );
}

/**
 * Get a link to add a gig to Google Calendar.
 *
 * @since 1.0.0
 *
 * @todo Need to add the artists' name to provide context in Google Calendar.
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string
 */
function get_audiotheme_gig_gcal_link( $post = null ) {
	$gig = get_audiotheme_gig( $post );

	$date = get_audiotheme_gig_time( 'Ymd', '', true );
	$time = get_audiotheme_gig_time( '', 'His', true );

	$dtstart  = $date;
	$dtstart .= ( empty( $time ) ) ? '' : 'T' . $time . 'Z';

	$location = '';
	if ( audiotheme_gig_has_venue( $gig ) ) {
		$venue = get_audiotheme_venue( $gig->venue->ID );

		$location  = $venue->name;
		$location .= ( empty( $venue->address ) ) ? '' : ', ' . esc_html( $venue->address );
		$location .= ( empty( $venue->city ) ) ? '' : ', ' . $venue->city;
		$location .= ( ! empty( $location ) && ! empty( $venue->state ) ) ? ', ' : '';
		$location .= ( empty( $venue->state ) ) ? '' : $venue->state;

		if ( ! empty( $venue->country ) ) {
			$location .= ( ! empty( $location ) ) ? ', ' : '';
			$location .= ( empty( $venue->country ) ) ? '' : $venue->country;
		}
	}

	$args = array(
		'action'   => 'TEMPLATE',
		'text'     => rawurlencode( wp_strip_all_tags( get_audiotheme_gig_title() ) ),
		'dates'    => $dtstart . '/' . $dtstart,
		'details'  => rawurlencode( wp_strip_all_tags( get_audiotheme_gig_description() ) ),
		'location' => rawurlencode( $location ),
		'sprop'    => rawurlencode( home_url( '/' ) ),
	);

	$link = add_query_arg( $args, 'http://www.google.com/calendar/event' );

	return $link;
}

/**
 * Display a link to add a gig to Google Calendar.
 *
 * @since 1.0.0
 */
function the_audiotheme_gig_gcal_link() {
	echo esc_url( get_audiotheme_gig_gcal_link() );
}

/**
 * Get a link to a gig's iCal endpoint.
 *
 * @since 1.0.0
 *
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string
 */
function get_audiotheme_gig_ical_link( $post = null ) {
	$post = get_post( $post );
	$permalink = get_option( 'permalink_structure' );

	$ical_link = get_permalink( $post );
	$ical_link = ( empty( $permalink ) ) ? add_query_arg( '', 'ical', $ical_link ) : trailingslashit( $ical_link ) . 'ical/';

	return $ical_link;
}

/**
 * Display the link to a gig's iCal endpoint.
 *
 * @since 1.0.0
 */
function the_audiotheme_gig_ical_link() {
	echo esc_url( get_audiotheme_gig_ical_link() );
}

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
 * Get the admin panel URL for gigs.
 *
 * @since 1.0.0
 */
function get_audiotheme_gig_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=audiotheme-gigs' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Update a gig's venue and the gig count for any modified venues.
 *
 * @since 1.0.0
 */
function set_audiotheme_gig_venue( $gig_id, $venue_name ) {
	$gig = get_audiotheme_gig( $gig_id ); // Retrieve current venue info.
	$venue_name = trim( stripslashes( $venue_name ) );

	if ( empty( $venue_name ) ) {
		p2p_delete_connections( 'audiotheme_venue_to_gig', array( 'to' => $gig_id ) );
	} elseif ( ! isset( $gig->venue->name ) || $venue_name != $gig->venue->name ) {
		p2p_delete_connections( 'audiotheme_venue_to_gig', array( 'to' => $gig_id ) );

		$new_venue = get_audiotheme_venue_by( 'name', $venue_name );
		if ( ! $new_venue ) {
			$new_venue = array(
				'name'      => $venue_name,
				'gig_count' => 1,
			);

			// Timezone is important, so retrieve it from the global $_POST array if it exists.
			if ( ! empty( $_POST['audiotheme_venue']['timezone_string'] ) ) {
				$new_venue['timezone_string'] = $_POST['audiotheme_venue']['timezone_string'];
			}

			$venue_id = save_audiotheme_venue( $new_venue );
			if ( $venue_id ) {
				p2p_create_connection( 'audiotheme_venue_to_gig', array(
					'from' => $venue_id,
					'to'   => $gig_id,
				) );
			}
		} else {
			$venue_id = $new_venue->ID;

			p2p_create_connection( 'audiotheme_venue_to_gig', array(
				'from' => $new_venue->ID,
				'to'   => $gig_id,
			) );

			update_audiotheme_venue_gig_count( $new_venue->ID );
		}
	}

	if ( isset( $gig->venue->ID ) ) {
		$venue_id = $gig->venue->ID;
		update_audiotheme_venue_gig_count( $venue_id );
	}

	return ( empty( $venue_id ) ) ? false : get_audiotheme_venue( $venue_id );
}

/**
 * Retrieve a venue by its ID.
 *
 * @since 1.0.0
 */
function get_audiotheme_venue( $post = null ) {
	if ( null === $post ) {
		$gig  = get_audiotheme_gig();
		$post = get_post( $gig->venue->ID );
	} else {
		$post = get_post( $post );
	}

	$defaults = get_default_audiotheme_venue_properties();
	$meta = (array) get_post_custom( $post->ID );
	foreach( $meta as $key => $val ) {
		$meta[ str_replace( '_audiotheme_', '', $key ) ] = $val;
		unset( $meta[ $key ] );
	}

	$properties = wp_parse_args( $meta, $defaults );

	foreach( $properties as $key => $prop ) {
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
 * Display or retrieve the link to the current venue's website.
 *
 * @since 1.0.0
 *
 * @param array $args Optional. Passed to get_audiotheme_venue_link().
 * @param bool $echo Optional. Default to true. Whether to display or return.
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
 * @param int $venue_id
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_link( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	if ( empty( $venue->name ) ) {
		return '';
	}

	extract( wp_parse_args( $args, array(
		'before'      => '',
		'after'       => '',
		'before_link' => '<span class="fn org" itemprop="name">',
		'after_link'  => '</span>',
	) ) );

	$html  = $before;
	$html .= ( empty( $venue->website ) ) ? '' : sprintf( '<a href="%s" class="url" itemprop="url">', esc_url( $venue->website ) );
	$html .= $before_link . $venue->name . $after_link;
	$html .= ( empty( $venue->website ) ) ? '' : '</a>';
	$html .= $after;

	return $html;
}

/**
 * Display or retrieve the current venue in vCard markup.
 *
 * @since 1.0.0
 *
 * @param array $args Optional. Passed to get_audiotheme_venue_vcard()
 * @param bool $echo Optional. Default to true. Whether to display or return.
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
 * @param int $venue_id
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_vcard( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	$args = wp_parse_args( $args, array(
		'container'         => 'dd',
		'show_country'      => true,
		'show_name'         => true,
		'show_name_link'    => true,
		'show_phone'        => true,
		'separator_address' => '<br>',
		'separator_country' => '<br>',
	) );

	$output  = '';

	if ( $args['show_name'] ) {
		$output .= ( empty( $venue->website ) || ! $args['show_name_link'] ) ? '' : '<a href="' . esc_url( $venue->website ) . '" class="url" itemprop="url">';
		$output .= '<span class="venue-name fn org" itemprop="name">' . $venue->name . '</span>';
		$output .= ( empty( $venue->website ) || ! $args['show_name_link'] ) ? '' : '</a>';
	}

	$address  = '';
	$address .= ( empty( $venue->address ) ) ? '' : '<span class="street-address" itemprop="streetAddress">' . esc_html( $venue->address ) . '</span>';

	$region  = '';
	$region .= ( empty( $venue->city ) ) ? '' : '<span class="locality" itemprop="addressLocality">' . $venue->city . '</span>';
	$region .= ( ! empty( $venue->city ) && ! empty( $venue->state ) ) ? ', ' : '';
	$region .= ( empty( $venue->state ) ) ? '' : '<span class="region" itempprop="addressRegion">' . $venue->state . '</span>';
	$region .= ( empty( $venue->postal_code ) ) ? '' : ' <span class="postal-code" itemprop="postalCode">' . $venue->postal_code . '</span>';

	$address .= ( ! empty( $address ) && ! empty( $region ) ) ? '<span class="sep sep-street-address">' . $args['separator_address'] . '</span>' : '';
	$address .= ( empty( $region ) ) ? '' : '<span class="venue-location">' . $region . '</span>';

	if ( ! empty( $venue->country ) && $args['show_country'] && apply_filters( 'show_audiotheme_venue_country', true ) ) {
		$country_class = esc_attr( 'country-name-' . sanitize_title_with_dashes( $venue->country ) );

		$address .= ( ! empty( $address ) && ! empty( $venue->country ) ) ? '<span class="sep sep-country-name ' . $country_class . '">' . $args['separator_country'] . '</span> ' : '';
		$address .= ( empty( $venue->country ) ) ? '' : '<span class="country-name ' . $country_class . '" itemprop="addressCountry">' . $venue->country . '</span>';
	}

	$output .= ( empty( $address ) ) ? '' : '<div class="venue-address adr" itemscope itemtype="http://schema.org/PostalAddress" itemprop="address">' . $address . '</div> ';

	if ( $args['show_phone'] ) {
		$output .= ( empty( $venue->phone ) ) ? '' : '<span class="venue-phone tel" itemprop="telephone">' . $venue->phone . '</span>';
	}

	if ( ! empty( $output ) && ! empty( $args['container'] ) ) {
		$container_open = '<' . $args['container'] . ' class="location vcard" itemprop="location" itemscope itemtype="http://schema.org/EventVenue">';
		$container_close = '</' . $args['container'] . '>';

		$output = $container_open . $output . $container_close;
	}

	return $output;
}

/**
 * Retrieve a venue's address as a string.
 *
 * @since 1.0.0
 *
 * @param int $venue_id
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_address( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	$address  = '';
	$address .= ( empty( $venue->address ) ) ? '' : trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $venue->address ) ) ) . ', ';

	$address .= ( empty( $venue->city ) ) ? '' : $venue->city;
	$address .= ( ! empty( $venue->city ) && ! empty( $venue->state ) ) ? ', ' : '';
	$address .= ( empty( $venue->state ) ) ? '' : $venue->state;
	$address .= ( empty( $venue->postal_code ) ) ? '' : ' ' . $venue->postal_code;

	return $address;
}

/**
 * Retrieve a venue's location (city, region, country).
 *
 * @since 1.1.0
 *
 * @param int $venue_id
 * @param array $args Optional. Override the defaults and modify the output structure.
 * @return string
 */
function get_audiotheme_venue_location( $venue_id, $args = array() ) {
	$venue = get_audiotheme_venue( $venue_id );

	$location  = '';
	$location .= ( empty( $venue->city ) ) ? '' : '<span class="locality">' . $venue->city . '</span>';
	$location .= ( ! empty( $location ) && ! empty( $venue->state ) ) ? '<span class="sep sep-region">,</span> ' : '';
	$location .= ( empty( $venue->state ) ) ? '' : '<span class="region">' . $venue->state . '</span>';

	if ( ! empty( $venue->country ) && apply_filters( 'show_audiotheme_venue_country', true ) ) {
		$country_class = esc_attr( 'country-name-' . sanitize_title_with_dashes( $venue->country ) );

		$location .= ( ! empty( $location ) ) ? '<span class="sep sep-country-name ' . $country_class . '">,</span> ' : '';
		$location .= ( empty( $venue->country ) ) ? '' : '<span class="country-name ' . $country_class . '">' . $venue->country . '</span>';
	}

	return $location;
}

/**
 * Get the base admin panel URL for adding a venue.
 *
 * @since 1.0.0
 */
function get_audiotheme_venue_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=audiotheme-venue' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Get the admin panel URL for viewing all venues.
 *
 * @since 1.0.0
 */
function get_audiotheme_venues_admin_url( $args = '' ) {
	$admin_url = admin_url( 'admin.php?page=audiotheme-venues' );

	if ( ! empty( $args ) ) {
		if ( is_array( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		} else {
			$admin_url = ( 0 !== strpos( $args, '&' ) ) ? '&' . $admin_url : $admin_url;
		}
	}

	return $admin_url;
}

/**
 * Get the admin panel URL for editing a venue.
 *
 * @since 1.0.0
 */
function get_audiotheme_venue_edit_link( $admin_url, $post_id ) {
	if ( 'audiotheme_venue' == get_post_type( $post_id ) ) {
		$args = array(
			'action'   => 'edit',
			'venue_id' => $post_id,
		);

		$admin_url = get_audiotheme_venue_admin_url( $args );
	}

	return $admin_url;
}

/**
 * Return a unique venue name.
 *
 * @since 1.0.0
 */
function get_unique_audiotheme_venue_name( $name, $venue_id = 0 ) {
	global $wpdb;

	$suffix = 2;
	while ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title=%s AND post_type='audiotheme_venue' AND ID!=%d", $name, $venue_id ) ) ) {
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
 */
function save_audiotheme_venue( $data ) {
	global $wpdb;

	$action = 'update';
	$current_user = wp_get_current_user();
	$defaults = get_default_audiotheme_venue_properties();

	// New venue.
	if ( empty( $data['ID'] ) ) {
		$action = 'insert';
		$data = wp_parse_args( $data, $defaults );
	} else {
		$current_venue = get_audiotheme_venue( $data['ID'] );
	}

	// Copy gig count before cleaning the data array.
	$gig_count = ( isset( $data['gig_count'] ) && is_numeric( $data['gig_count'] ) ) ? absint( $data['gig_count'] ) : 0;

	// Remove properties that aren't whitelisted.
	$data = array_intersect_key( $data, $defaults );

	// Map the 'name' property to the 'post_title' field.
	if ( isset( $data['name'] ) && ! empty( $data['name'] ) ) {
		$post_title = get_unique_audiotheme_venue_name( $data['name'], $data['ID'] );

		if ( ! isset( $current_venue ) || $post_title != $current_venue->name ) {
			$venue['post_title'] = $post_title;
			$venue['post_name'] = '';
		}
	}

	// Insert the post container.
	if ( 'insert' == $action ) {
		$venue['post_author'] = $current_user->ID;
		$venue['post_status'] = 'publish';
		$venue['post_type'] = 'audiotheme_venue';

		$venue_id = wp_insert_post( $venue );
	} else {
		$venue_id = absint( $data['ID'] );

		if ( ! empty( $venue['post_title'] ) ) {
			$venue['ID'] = $venue_id;
			wp_update_post( $venue );
		}
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

		foreach ( $data as $key => $val ) {
			$key = '_audiotheme_' . $key;
			update_post_meta( $venue_id, $key, $val );
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
 */
function update_audiotheme_venue_gig_count( $venue_id, $count = 0 ) {
	global $wpdb;

	if ( ! $count ) {
		$count = get_audiotheme_venue_gig_count( $venue_id );
	}

	update_post_meta( $venue_id, '_audiotheme_gig_count', absint( $count ) );
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
 * @param int $venue_id Optional. Venue ID.
 * @return string
 */
function get_audiotheme_google_map_embed( $args = array(), $venue_id = 0 ) {
	$args = wp_parse_args( $args, array(
		'address'   => '',
		'width'     => '100%',
		'height'    => 300,
		'link_text' => __( 'Get Directions', 'audiotheme' ),
		'format'    => '%1$s<p class="venue-map-link">%2$s</p>',
	) );

	// Get the current post and determine if it's a gig with a venue.
	if ( empty( $args['address'] ) && ( $gig = get_audiotheme_gig() ) ) {
		if ( 'audiotheme_gig' == get_post_type( $gig ) && ! empty( $gig->venue->ID ) ) {
			$venue_id = $gig->venue->ID;
		}
	}

	// Retrieve the address for the venue.
	if ( $venue_id ) {
		$venue = get_audiotheme_venue( $venue_id );

		$args['address'] = get_audiotheme_venue_address( $venue->ID );
		$args['address'] = ( $args['address'] ) ? $venue->name . ', ' . $args['address'] : $venue->name;
	}

	$src = add_query_arg( array(
		'f'       => 'q',
		'source'  => 's_q',
		'hl'      => 'en',
		'geocode' => '',
		'q'       => rawurlencode( $args['address'] ),
		'output'  => 'embed',
		'iwloc'   => '',
	), 'http://maps.google.com/maps' );

	$iframe = sprintf( '<iframe src="%s" width="%s" height="%s" frameBorder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>',
		esc_url( $src ),
		esc_attr( $args['width'] ),
		esc_attr( $args['height'] )
	);

	$link = sprintf( '<a href="%s" target="_blank">%s</a>',
		esc_url( add_query_arg( 'q', urlencode( $args['address'] ), 'http://maps.google.com/maps' ) ),
		$args['link_text']
	);

	return sprintf( $args['format'], $iframe, $link );
}
