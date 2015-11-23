/*jshint browserify:true */

'use strict';

var Venues,
	Backbone = require( 'backbone' ),
	Venue = require( './venue' );

Venues = Backbone.Collection.extend({
	model: Venue,

	comparator: function( model ) {
		return model.get( 'name' ).toLowerCase();
	}
});

module.exports = Venues;
