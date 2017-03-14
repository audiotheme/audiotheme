/* jshint browserify: true */

'use strict';

var VenueAddController,
	l10n = require( 'audiotheme' ).l10n,
	Venue = require( '../models/venue' ),
	wp = require( 'wp' );

VenueAddController = wp.media.controller.State.extend({
	defaults: {
		id:      'venue-add',
		title:   l10n.addNewVenue || 'Add New Venue',
		button:  {
			text: l10n.save || 'Save'
		},
		content: 'venue-add',
		menu:    'default',
		menuItem: {
			text: l10n.addVenue || 'Add a Venue',
			priority: 20
		},
		toolbar: 'venue-add'
	},

	initialize: function() {
		this.set( 'model', new Venue() );
	}
});

module.exports = VenueAddController;
