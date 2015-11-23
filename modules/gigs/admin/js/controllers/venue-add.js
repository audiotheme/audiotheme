/*jshint browserify:true */

'use strict';

var VenueAddController,
	l10n = require( 'audiotheme' ).l10n,
	Venue = require( '../models/venue' ),
	wp = require( 'wp' );

VenueAddController = wp.media.controller.State.extend({
	defaults: {
		id:      'audiotheme-venue-add',
		menu:    'audiotheme-venues',
		content: 'audiotheme-venue-add',
		toolbar: 'audiotheme-venue-add',
		title:   l10n.addNewVenue || 'Add New Venue',
		button:  {
			text: l10n.save || 'Save'
		},
		menuItem: {
			text: l10n.addVenue || 'Add a Venue',
			priority: 20
		}
	},

	initialize: function() {
		this.set( 'model', new Venue() );
	}
});

module.exports = VenueAddController;
