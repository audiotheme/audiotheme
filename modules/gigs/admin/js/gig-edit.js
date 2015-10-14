/*jshint browserify:true */
/*global _audiothemeGigEditSettings:false, _pikadayL10n:false, isRtl:false, Pikaday:false */

'use strict';

var frame, settings, wpScreen,
	$ = require( 'jquery' ),
	app = require( 'audiotheme' ),
	Backbone = require( 'backbone' ),
	$date = $( '#gig-date' ),
	$time = $( '#gig-time' ),
	ss = sessionStorage || {},
	lastGigDate = 'lastGigDate' in ss ? new Date( ss.lastGigDate ) : null,
	lastGigTime = 'lastGigTime' in ss ? new Date( ss.lastGigTime ) : null,
	$venueIdField = $( '#gig-venue-id' );

settings = app.settings( _audiothemeGigEditSettings );

app.view.GigVenueMetaBox = require( './views/meta-box/gig-venue' );
app.view.GigVenueDetails = require( './views/gig-venue-details' );
app.view.GigVenueSelectButton = require( './views/button/gig-venue-select' );

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
	var date = $date.datepicker( 'getDate' ),
		time = $time.timepicker( 'getTime' );

	if ( ss && '' !== date ) {
		ss.lastGigDate = date;
	}

	if ( ss && '' !== time ) {
		ss.lastGigTime = time;
	}
});

// Initialize the date picker.
new Pikaday({
	bound: false,
	container: document.getElementById( 'audiotheme-gig-start-date-picker' ),
	field: $( '.audiotheme-gig-date-picker-start' ).find( 'input' ).get( 0 ),
	format: 'YYYY/MM/DD',
	i18n: _pikadayL10n || {},
	isRTL: isRtl,
	theme: 'audiotheme-pikaday'
});

// Initialize the venue frame.
frame = new app.view.VenueFrame({
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
	venue: new app.model.Venue( settings.venue || {} )
});

new app.view.GigVenueMetaBox({
	controller: wpScreen
}).render();
