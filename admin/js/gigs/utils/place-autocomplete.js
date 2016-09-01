/*jshint browserify:true */
/*global google:false */

'use strict';

var settings,
	$ = require( 'jquery' ),
	_ = require( 'underscore' ),
	app = require( 'audiotheme' );

settings = app.settings();

function getAddress( components ) {
	var map,
		address = {};

	map = {
		street_number: 'short_name',
		route: 'long_name',
		locality: 'long_name',
		administrative_area_level_1: 'short_name',
		country: 'long_name',
		postal_code: 'short_name'
	 };

	 _.each( components, function( component ) {
		var type = component.types[0];

		if ( map[ type ] ) {
			address[ type ] = component[ map[ type ] ];
		}
	});

	return address;
}

function updateTimeZone( $field, latitude, longitude ) {
	return $.ajax({
		url: 'https://maps.googleapis.com/maps/api/timezone/json',
		data: {
			location: latitude + ',' + longitude,
			key: settings.googleMapsApiKey,
			timestamp: parseInt( Math.floor( Date.now() / 1000 ), 10 )
		}
	})
	.done(function( response ) {
		$field
			.find( 'option[value="' + response.timeZoneId + '"]' ).attr( 'selected', true )
			.end()
			.trigger( 'change' );
	});
}

/*
 * Currently used in:
 * - views/venue/add-form.js
 * - views/venue/edit-form.js
 */
module.exports = function( options ) {
	var autocomplete,
		fields = options.fields || {};

	autocomplete = new google.maps.places.Autocomplete( options.input, {
		types: [ options.type ]
	});

	autocomplete.addListener( 'place_changed', function() {
		var place = autocomplete.getPlace(),
			address = getAddress( place.address_components ),
			location = place.geometry.location;

		if ( fields.name ) {
			fields.name.val( place.name ).trigger( 'change' );
		}

		if ( fields.address ) {
			fields.address.val( address.street_number + ' ' + address.route ).trigger( 'change' );
		}

		if ( fields.city ) {
			fields.city.val( address.locality ).trigger( 'change' );
		}

		if ( fields.state ) {
			fields.state.val( address.administrative_area_level_1 ).trigger( 'change' );
		}

		if ( fields.postalCode ) {
			fields.postalCode.val( address.postal_code ).trigger( 'change' );
		}

		if ( fields.country ) {
			fields.country.val( address.country ).trigger( 'change' );
		}

		if ( fields.phone ) {
			fields.phone.val( place.formatted_phone_number ).trigger( 'change' );
		}

		if ( fields.website ) {
			fields.website.val( place.website ).trigger( 'change' );
		}

		if ( fields.timeZone ) {
			updateTimeZone(
				fields.timeZone,
				location.lat(),
				location.lng()
			);
		}
	});
};
