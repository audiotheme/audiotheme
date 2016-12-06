/*jshint browserify:true */
/*global _audiothemeGigEditSettings:false, _audiothemeVenueManagerSettings:false, _pikadayL10n:false, isRtl:false, Pikaday:false */

'use strict';

var datepicker, frame, settings, wpScreen,
	$ = require( 'jquery' ),
	app = require( 'audiotheme' ),
	Backbone = require( 'backbone' ),
	$time = $( '#gig-time' ),
	ss = sessionStorage || {},
	lastGigDate = 'lastGigDate' in ss ? new Date( ss.lastGigDate ) : null,
	lastGigTime = 'lastGigTime' in ss ? new Date( ss.lastGigTime ) : null,
	$venueIdField = $( '#gig-venue-id' );

var GigVenueMetaBox = require( './gigs/views/meta-box/gig-venue' ),
	Venue = require( './gigs/models/venue' ),
	VenueFrame = require( './gigs/views/frame/venue' );

settings = app.settings( _audiothemeGigEditSettings );
settings = app.settings( _audiothemeVenueManagerSettings );

// Add a day to the last saved gig date.
if ( lastGigDate ) {
	lastGigDate.setDate( lastGigDate.getDate() + 1 );
}

// Initialize the time picker.
$time.timepicker({
	'scrollDefaultTime': lastGigTime || '',
	'timeFormat': settings.timeFormat,
	'className': 'ui-autocomplete'
}).on( 'showTimepicker', function() {
	$( this ).addClass( 'open' );
	$( '.ui-timepicker-list' ).width( $( this ).outerWidth() );
}) .on( 'hideTimepicker', function() {
	$( this ).removeClass( 'open' );
}) .next().on( 'click', function() {
	$time.focus();
});

// Add the last saved date and time to session storage
// when the gig is saved.
$( '#publish' ).on( 'click', function() {
	var date = datepicker.getDate(),
		time = $time.timepicker( 'getTime' );

	if ( ss && '' !== date ) {
		ss.lastGigDate = date;
	}

	if ( ss && '' !== time ) {
		ss.lastGigTime = time;
	}
});

// Initialize the date picker.
datepicker = new Pikaday({
	bound: false,
	container: document.getElementById( 'audiotheme-gig-start-date-picker' ),
	field: $( '.audiotheme-gig-date-picker-start' ).find( 'input' ).get( 0 ),
	format: 'YYYY/MM/DD',
	i18n: _pikadayL10n || {},
	isRTL: isRtl,
	theme: 'audiotheme-pikaday'
});

// Initialize the venue frame.
frame = new VenueFrame({
	title: app.l10n.venues || 'Venues',
	button: {
		text: app.l10n.selectVenue || 'Select Venue'
	}
});

// Refresh venue in case data was edited in the modal.
frame.on( 'close', function() {
	wpScreen.get( 'venue' ).fetch();
});

frame.on( 'insert', function( selection ) {
	wpScreen.set( 'venue', selection.first() );
	$venueIdField.val( selection.first().get( 'ID' ) );
});

wpScreen = new Backbone.Model({
	frame: frame,
	venue: new Venue( settings.venue || {} )
});

new GigVenueMetaBox({
	controller: wpScreen
}).render();

$( window ).on( 'keyup', function( e ) {
	// Only handle key events when the venue list state is active.
	if ( ! frame.$el.is( ':visible' ) || 'venues' !== frame.state().id ) {
		return;
	}

	// Up arrow.
	if ( 38 === e.keyCode ) {
		frame.state().previous();
	}

	// Down arrow.
	if ( 40 === e.keyCode ) {
		frame.state().next();
	}
});
