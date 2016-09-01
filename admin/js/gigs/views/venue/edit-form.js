/*jshint browserify:true */

'use strict';

var VenueEditForm,
	$ = require( 'jquery' ),
	wp = require( 'wp' ),
	placeAutocomplete = require( '../../utils/place-autocomplete' );

VenueEditForm = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venue-edit-form',
	template: wp.template( 'audiotheme-venue-edit-form' ),

	events: {
		'change [data-setting]': 'updateAttribute'
	},

	initialize: function( options ) {
		this.model = options.model;
		this.$spinner = $( '<span class="spinner"></span>' );
	},

	render: function() {
		var tzString = this.model.get( 'timezone_string' );

		this.$el.html( this.template( this.model.toJSON() ) );

		if ( tzString ) {
			this.$el.find( '#venue-timezone-string' ).find( 'option[value="' + tzString + '"]' ).prop( 'selected', true );
		}

		placeAutocomplete({
			input: this.$( '[data-setting="name"]' )[0],
			fields: {
				name: this.$( '[data-setting="name"]' ),
				address: this.$( '[data-setting="address"]' ),
				city: this.$( '[data-setting="city"]' ),
				state: this.$( '[data-setting="state"]' ),
				postalCode: this.$( '[data-setting="postal_code"]' ),
				country: this.$( '[data-setting="country"]' ),
				timeZone: this.$( '[data-setting="timezone_string"]' ),
				phone: this.$( '[data-setting="phone"]' ),
				website: this.$( '[data-setting="website"]' )
			},
			type: 'establishment'
		});

		placeAutocomplete({
			input: this.$( '[data-setting="city"]' )[0],
			fields: {
				city: this.$( '[data-setting="city"]' ),
				state: this.$( '[data-setting="state"]' ),
				country: this.$( '[data-setting="country"]' ),
				timeZone: this.$( '[data-setting="timezone_string"]' ),
			},
			type: '(cities)'
		});

		return this;
	},

	/**
	 * Update a model attribute when a field is changed.
	 *
	 * Fields with a 'data-setting="{{key}}"' attribute whose value
	 * corresponds to a model attribute will be automatically synced.
	 *
	 * @param {Object} e Event object.
	 */
	updateAttribute: function( e ) {
		var $target = $( e.target ),
			attribute = $target.data( 'setting' ),
			value = e.target.value,
			$spinner = this.$spinner;

		if ( this.model.get( attribute ) !== value ) {
			$spinner.insertAfter( $target ).addClass( 'is-active' );

			this.model.set( attribute, value ).save().always(function() {
				$spinner.removeClass( 'is-active' );
			});
		}
	}
});

module.exports = VenueEditForm;
