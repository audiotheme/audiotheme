/*jshint browserify:true */
/*global isRtl:false */

'use strict';

var settings,
	$ = require( 'jquery' ),
	app = require( 'audiotheme' ),
	position = { offset: '0, -1' };

settings = app.settings();

if ( 'undefined' !== typeof isRtl && isRtl ) {
	position = {
		my: 'right top',
		at: 'right bottom',
		offset: '0, -1'
	};
}

/**
 * This will likely be updated to remove the jQuery UI autocomplete dependency
 * and use event delegation, so don't use it as a dependency.
 *
 * Currently used in:
 * - venue-edit.js
 * - views/venue/add-form.js
 * - views/venue/edit-form.js
 */
module.exports = function( $city, $state, $country, $timezone ) {
	$city.autocomplete({
		source: function( request, callback ) {
			$.ajax({
				url: 'https://gazetteer.audiotheme.com/api/v1/city/search',
				data: {
					q: request.term,
					include: 'timezone'
				}
			})
			.done(function( response ) {
				callback( $.map( response.results, function( item ) {
					var location = item.location || {},
						label = item.name;

					if ( location.region_code ) {
						label += ', ' + location.region_code;
					} else if ( location.region ) {
						label += ', ' + location.region;
					}

					label += ', ' + location.country;

					return {
						label: label,
						value: item.name,
						region: location.region,
						region_code: location.region_code,
						country: location.country,
						country_code: location.country_code,
						timezoneId: item.timezone.id
					};
				}) );
			})
			.fail(function() {
				callback();
			});
		},
		minLength: 2,
		position: position,
		select: function( e, ui ) {
			if ( $state && '' === $state.val() ) {
				$state.val( ui.item.region_code || ui.item.region ).trigger( 'change' );
			}

			if ( $country && '' === $country.val() ) {
				$country.val( ui.item.country ).trigger( 'change' );
			}

			if ( $timezone ) {
				$timezone.find( 'option[value="' + ui.item.timezoneId + '"]' ).attr( 'selected', true );
				$timezone.trigger( 'change' );
			}
		},
		change: function( event, ui ) {
			$( this ).trigger( 'change' );
		},
		open: function() {
			$( this ).addClass( 'open' );
		},
		close: function() {
			$( this ).removeClass( 'open' );
		}
	});
};
