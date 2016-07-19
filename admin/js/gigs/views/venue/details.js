/*jshint browserify:true */

'use strict';

var VenueDetails,
	_ = require( 'underscore' ),
	templateHelpers = require( '../../utils/template-helpers' ),
	wp = require( 'wp' );

VenueDetails = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venue-details',
	template: wp.template( 'audiotheme-venue-details' ),

	render: function() {
		var model = this.controller.state( 'venues' ).get( 'selection' ).first(),
			data = _.extend( model.toJSON(), templateHelpers );

		this.$el.html( this.template( data ) );
		return this;
	}
});

module.exports = VenueDetails;
