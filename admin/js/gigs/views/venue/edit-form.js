/*jshint browserify:true */

'use strict';

var VenueEditForm,
	$ = require( 'jquery' ),
	wp = require( 'wp' ),
	cityTypeahead = require( '../../utils/city-typeahead' );

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

		cityTypeahead(
			this.$el.find( '[data-setting="city"]' ),
			this.$el.find( '[data-setting="state"]' ),
			this.$el.find( '[data-setting="country"]' ),
			this.$el.find( '[data-setting="timezone_string"]' )
		);

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
