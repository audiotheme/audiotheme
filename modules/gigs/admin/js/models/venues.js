var Venues,
	_ = require( 'underscore' ),
	Backbone = require( 'backbone' ),
	Venue = require( './venue' ),
	wp = require( 'wp' );

Venues = Backbone.Collection.extend({
	model: Venue,

	comparator: function( model ) {
		return model.get( 'name' );
	}
});

module.exports = Venues;
