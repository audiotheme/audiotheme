/*jshint browserify:true */

'use strict';

var VenueAddContent,
	VenueAddForm = require( '../venue-add-form' ),
	wp = require( 'wp' );

VenueAddContent = wp.media.View.extend({
	className: 'audiotheme-venue-frame-content audiotheme-venue-frame-content--add',

	render: function() {
		this.views.add([
			new VenueAddForm({
				controller: this.controller,
				model: this.controller.state( 'audiotheme-venue-add' ).get( 'model' )
			})
		]);
		return this;
	}
});

module.exports = VenueAddContent;
