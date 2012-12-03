<?php
/**
 * Gigs JSON feed template.
 *
 * @package AudioTheme_Framework
 * @subpackage Gigs
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
		// @todo Attempt to add a property to display the date in UTC.
	}
	
	$events[] = $event;
}

echo json_encode( $events );
?>