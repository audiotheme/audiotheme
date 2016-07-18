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

	// Determine the gig time.
	$gig_time = get_post_meta( $post->ID, '_audiotheme_gig_time', true );
	$t = date_parse( $gig_time );
	if ( empty( $t['errors'] ) ) {
		$post->gig_time = mysql2date( get_option( 'time_format' ), $post->gig_datetime );
	}

	$post->venue = null;
	if ( isset( $post->connected[0] ) && isset( $post->connected[0]->ID ) ) {
		$post->venue = get_audiotheme_venue( $post->connected[0]->ID );
	} elseif ( ! empty( $post->_audiotheme_venue_id ) ) {
		$post->venue = get_audiotheme_venue( $post->_audiotheme_venue_id );
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
 * @param array $args Optional. Passed to get_audiotheme_gig_link().
 * @param bool  $echo Optional. Default to true. Whether to display or return.
 * @return string|null Null on failure or display. String when echo is false.
 */
function the_audiotheme_gig_link( $args = array(), $echo = true ) {
	$html = get_audiotheme_gig_link( null, $args );

	if ( $echo ) {
		echo $html; }
	else {
		return $html; }
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
 * @param array      $args Optional. Override the defaults and modify the output structure.
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
		// Only get the date portion since the time portion is unknown.
		$string_gmt = mysql2date( 'Y-m-d', $gig->gig_datetime );
	}

	return $string_gmt;
}

/**
 * Display a gig's date and time.
 *
 * @since 1.3.0
 *
 * @param string     $d Optional. PHP date format.
 * @param string     $t Optional. PHP time format.
 * @param bool       $gmt Optional, default is false. Whether to return the gmt time.
 * @param array      $args Optional. Override the defaults.
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
 * @param string     $d Optional. PHP date format.
 * @param string     $t Optional. PHP time format.
 * @param bool       $gmt Optional, default is false. Whether to return the gmt time.
 * @param array      $args Optional. Override the defaults.
 * @param int|object $post Optional post ID or object. Default is global $post object.
 * @return string
 */
function get_audiotheme_gig_time( $d = 'c', $t = '', $gmt = false, $args = null, $post = null ) {
	$args = wp_parse_args( $args, array(
		'empty_time' => '', // Displays if time hasn't been saved.
		'translate'  => true,
	) );
	extract( $args, EXTR_SKIP );

	$gig = get_audiotheme_gig( $post );

	if ( empty( $gig->gig_time ) ) {
		// ISO 8601 without time component or timezone component.
		// @todo Need to verify Google Calendar support.
		$d = ( 'c' === $d ) ? 'Y-m-d' : $d;
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
 * @param bool   $echo Optional, default to true. Whether to display or return.
 * @return null|string Null on no description. String if $echo parameter is false.
 */
function the_audiotheme_gig_description( $before = '', $after = '', $echo = true ) {
	$description = get_audiotheme_gig_description();

	$html = ( empty( $description ) ) ? '' : $before . wpautop( $description ) . $after;

	if ( $echo ) {
		echo $html; }
	else {
		return $html; }
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
 * @param string     $key Check for a particular type of meta. Defaults to any.
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

	$link = add_query_arg( $args, 'https://www.google.com/calendar/event' );

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
