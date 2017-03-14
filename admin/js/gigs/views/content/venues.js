/* jshint browserify: true */

'use strict';

var VenuesContent,
	VenuePanel = require( '../venue/panel' ),
	VenuesList = require( '../venues-list' ),
	VenuesSearch = require( '../venues-search' ),
	wp = require( 'wp' );

VenuesContent = wp.media.View.extend({
	className: 'audiotheme-venue-frame-content',

	initialize: function( options ) {
		var view = this,
			selection = this.controller.state( 'venues' ).get( 'selection' );

		if ( ! this.collection.length ) {
			this.collection.fetch().done(function() {
				if ( ! selection.length ) {
					selection.reset( view.collection.first() );
				}
			});
		}
	},

	render: function() {
		this.views.add([
			new VenuesSearch({
				controller: this.controller
			}),
			new VenuesList({
				controller: this.controller,
				collection: this.collection
			}),
			new VenuePanel({
				controller: this.controller
			})
		]);

		return this;
	}
});

module.exports = VenuesContent;
