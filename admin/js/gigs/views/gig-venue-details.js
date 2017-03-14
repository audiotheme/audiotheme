/* jshint browserify: true */

'use strict';

var GigVenueDetails,
	_ = require( 'underscore' ),
	templateHelpers = require( '../utils/template-helpers' ),
	wp = require( 'wp' );

GigVenueDetails = wp.media.View.extend({
	className: 'audiotheme-gig-venue-details',
	template: wp.template( 'audiotheme-gig-venue-details' ),

	initialize: function( options ) {
		this.model = options.model;

		this.listenTo( this.model, 'change', this.render );
	},

	render: function() {
		var data;

		if ( this.model.get( 'ID' ) ) {
			data = _.extend( this.model.toJSON(), templateHelpers );
			this.$el.html( this.template( data ) );
		} else {
			this.$el.empty();
		}

		return this;
	}
});

module.exports = GigVenueDetails;
