var GigVenueDetails,
	templateHelpers = require( '../utils/template-helpers' ),
	wp = require( 'wp' );

GigVenueDetails = wp.media.View.extend({
	className: 'audiotheme-gig-venue-details',
	template: wp.template( 'audiotheme-gig-venue-details' ),

	initialize: function( options ) {
		this.listenTo( this.controller, 'change:venue', this.render );
		this.listenTo( this.controller.get( 'venue' ), 'change', this.render );
	},

	render: function() {
		var data, model = this.controller.get( 'venue' );

		if ( model.get( 'ID' ) ) {
			data = _.extend( model.toJSON(), templateHelpers );
			this.$el.html( this.template( data ) );
		} else {
			this.$el.empty();
		}

		return this;
	}
});

module.exports = GigVenueDetails;
