/* jshint browserify: true */

'use strict';

var GigVenueMetaBox,
	GigVenueDetails = require( '../gig-venue-details' ),
	GigVenueSelectButton = require( '../button/gig-venue-select' ),
	wp = require( 'wp' );

GigVenueMetaBox = wp.media.View.extend({
	el: '#audiotheme-gig-venue-meta-box',

	initialize: function( options ) {
		this.controller = options.controller;

		this.listenTo( this.controller, 'change:venue', this.render );
		this.controller.get( 'frame' ).on( 'open', this.updateSelection, this );
	},

	render: function() {
		this.views.set( '.audiotheme-panel-body', [
			new GigVenueDetails({
				model: this.controller.get( 'venue' )
			}),
			new GigVenueSelectButton({
				controller: this.controller
			})
		]);

		return this;
	},

	updateSelection: function() {
		var frame = this.controller.get( 'frame' ),
			model = this.controller.get( 'venue' ),
			venues = frame.states.get( 'venues' ).get( 'venues' ),
			selection = frame.states.get( 'venues' ).get( 'selection' );

		if ( model.get( 'ID' ) ) {
			venues.add( model, { at: 0 });
			selection.reset( model );
		}
	}
});

module.exports = GigVenueMetaBox;
