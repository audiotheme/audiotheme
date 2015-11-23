/*jshint browserify:true */

'use strict';

var VenueAddForm,
	$ = require( 'jquery' ),
	wp = require( 'wp' ),
	cityTypeahead = require( '../utils/city-typeahead' );

/**
 *
 *
 * @todo Search for timezone based on the city.
 * @todo Display an error if the timezone isn't set.
 */
VenueAddForm = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venue-edit-form',
	template: wp.template( 'audiotheme-venue-edit-form' ),

	events: {
		'change [data-setting]': 'updateAttribute'
	},

	initialize: function( options ) {
		this.model = options.model;
	},

	render: function() {
		this.$el.html( this.template( this.model.toJSON() ) );

		cityTypeahead(
			this.$el.find( '[data-setting="city"]' ),
			this.$el.find( '[data-setting="state"]' ),
			this.$el.find( '[data-setting="country"]' ),
			this.$el.find( '[data-setting="timezone_string"]' )
		);

		//this.$button = this.controller.toolbar.view.views.first( '.media-frame-toolbar' ).primary.get( 'save' ).$el;
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
		var attribute = $( e.target ).data( 'setting' ),
			value = e.target.value;

		if ( this.model.get( attribute ) !== value ) {
			this.model.set( attribute, value );
		}
	}
});

module.exports = VenueAddForm;
