var GigVenueMetaBox,
	GigVenueDetails = require( '../gig-venue-details' ),
	GigVenueSelectButton = require( '../button/gig-venue-select' ),
	wp = require( 'wp' );

GigVenueMetaBox = wp.media.View.extend({
	el: '#audiotheme-gig-venue-meta-box',

	initialize: function( options ) {
		this.controller = options.controller;
		this.controller.get( 'frame' ).on( 'open', this.updateSelection, this );
	},

	render: function() {
		this.views.add( '.audiotheme-panel-body', [
			new GigVenueDetails({
				controller: this.controller
			}),
			new GigVenueSelectButton({
				controller: this.controller
			})
		]);

		return this;
	},

	updateSelection: function() {
		var frame = this.controller.get( 'frame' ),
			venue = this.controller.get( 'venue' ),
			venues = frame.states.get( 'audiotheme-venues' ).get( 'venues' ),
			selection = frame.states.get( 'audiotheme-venues' ).get( 'selection' );

		if ( venue.get( 'ID' ) ) {
			venues.add( venue, { at: 0 });
			selection.reset( venue );
		}
	}
});

module.exports = GigVenueMetaBox;
