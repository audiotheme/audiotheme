<?php
/**
 * Gigs JSON feed template.
 *
 * @todo Attempt to add a property to display the date in UTC.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.0.0
 */

@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

foreach ( $wp_query->posts as $post ) {
	$post = get_audiotheme_gig( $post );

	$event = new stdClass;
	$event->id = $post->ID;
	$event->title = $post->post_title;
	$event->description = $post->post_excerpt;
	$event->url = get_permalink( $post->ID );
	$event->start->date = get_audiotheme_gig_time( 'Y-m-d' );
	$event->start->time = get_post_meta( $post->ID, '_audiotheme_gig_time', true );
	$event->start->datetime = get_audiotheme_gig_time( 'c', '', true );

	if ( ! empty( $post->venue ) ) {
		$event->venue->ID = $post->venue->ID;
		$event->venue->name = $post->venue->name;
		$event->venue->url = $post->venue->website;
		$event->venue->phone = $post->venue->phone;

		$event->venue->location->street = $post->venue->address;
		$event->venue->location->city = $post->venue->city;
		$event->venue->location->state = $post->venue->state;
		$event->venue->location->postalcode = $post->venue->postal_code;
		$event->venue->location->country = $post->venue->country;

		$event->venue->location->timezone = $post->venue->timezone_string;
	}

	$events[] = $event;
}

echo is_singular() ? wp_json_encode( $events[0] ) : wp_json_encode( $events );
