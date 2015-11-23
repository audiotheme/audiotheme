/*jshint browserify:true */

'use strict';

var GigVenueSelectButton,
	l10n = require( 'audiotheme' ).l10n,
	wp = require( 'wp' );

GigVenueSelectButton = wp.media.View.extend({
	className: 'button',
	tagName: 'button',

	events: {
		'click': 'openModal'
	},

	render: function() {
		this.$el.text( l10n.selectVenue || 'Select Venue' );
		return this;
	},

	openModal: function( e ) {
		e.preventDefault();
		this.controller.get( 'frame' ).open();
	}
});

module.exports = GigVenueSelectButton;
