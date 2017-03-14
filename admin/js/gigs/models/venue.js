/* jshint browserify: true */

'use strict';

var Venue,
	_ = require( 'underscore' ),
	Backbone = require( 'backbone' ),
	settings = require( 'audiotheme' ).settings(),
	wp = require( 'wp' );

Venue = Backbone.Model.extend({
	idAttribute: 'ID',

	defaults: {
		ID: null,
		name: '',
		address: '',
		city: '',
		state: '',
		postal_code: '',
		country: '',
		phone: '',
		timezone_string: settings.defaultTimezoneString || '',
		website: ''
	},

	sync: function( method, model, options ) {
		options = options || {};
		options.context = this;

		if ( 'create' === method ) {
			if ( ! settings.canPublishVenues || ! settings.insertVenueNonce ) {
				return Backbone.$.Deferred().rejectWith( this ).promise();
			}

			options.data = _.extend( options.data || {}, {
				action: 'audiotheme_ajax_save_venue',
				model: model.toJSON(),
				nonce: settings.insertVenueNonce
			});

			return wp.ajax.send( options );
		}

		// If the attachment does not yet have an `ID`, return an instantly
		// rejected promise. Otherwise, all of our requests will fail.
		if ( _.isUndefined( this.id ) ) {
			return Backbone.$.Deferred().rejectWith( this ).promise();
		}

		// Overload the `read` request so Venue.fetch() functions correctly.
		if ( 'read' === method ) {
			options.data = _.extend( options.data || {}, {
				action: 'audiotheme_ajax_get_venue',
				ID: this.id
			});
			return wp.ajax.send( options );
		} else if ( 'update' === method ) {
			// If we do not have the necessary nonce, fail immeditately.
			if ( ! this.get( 'nonces' ) || ! this.get( 'nonces' ).update ) {
				return Backbone.$.Deferred().rejectWith( this ).promise();
			}

			// Set the action and ID.
			options.data = _.extend( options.data || {}, {
				action: 'audiotheme_ajax_save_venue',
				nonce: this.get( 'nonces' ).update
			});

			// Record the values of the changed attributes.
			if ( model.hasChanged() ) {
				options.data.model = model.changed;
				options.data.model.ID = this.id;
			}

			return wp.ajax.send( options );
		}
	}
});

module.exports = Venue;
