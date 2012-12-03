<?php
/**
 * Gigs iCal feed template.
 *
 * @package AudioTheme_Framework
 * @subpackage Gigs
 */

header( 'Content-type: text/calendar' );
header( 'Content-Disposition: attachment; filename="audiotheme-gigs.ics"' );	
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//AudioTheme <?php echo AUDIOTHEME_VERSION; ?>

<?php
foreach ( $wp_query->posts as $post ) {
	$post = get_audiotheme_gig( $post );
	
	echo "BEGIN:VEVENT\n";
	echo "UID:" . get_the_guid( $post->ID ) . "\n";
	echo "URL:" . get_permalink( $post->ID ) . "\n";
	
	$date = get_audiotheme_gig_time( 'Ymd', '', true );
	$time = get_audiotheme_gig_time( '', 'His', true );
	$dtstart = sprintf( "DTSTART%s%s%s\n",
		( empty( $time ) ) ? ';VALUE=DATE:' : ';TZID=GMT:',
		$date,
		( empty( $time ) ) ? '' : 'T' . $time );
	echo $dtstart;
	
	echo "SUMMARY:" . get_audiotheme_gig_title() . "\n";
	
	if ( ! empty( $post->post_excerpt ) ) {
		echo "DESCRIPTION:" . escape_ical_text( $post->post_excerpt ) . "\n";
	}
	
	if ( ! empty( $post->venue ) ) {
		$location = get_audiotheme_venue_location_ical( $post->venue->ID );
		echo ( empty( $location ) ) ? '' : 'LOCATION:' . $location . "\n";
	}
	
	echo "END:VEVENT\n";
}
?>
END:VCALENDAR