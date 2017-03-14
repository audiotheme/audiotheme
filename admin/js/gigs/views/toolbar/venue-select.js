/* jshint browserify: true */

'use strict';

var VenueSelectToolbar,
	_ = require( 'underscore' ),
	wp = require( 'wp' );

VenueSelectToolbar = wp.media.view.Toolbar.extend({
	initialize: function( options ) {
		var selection = options.selection;

		this.controller = options.controller;

		// This is a button.
		this.options.items = _.defaults( this.options.items || {}, {
			select: {
				text: this.controller.state().get( 'button' ).text,
				style: 'primary',
				priority: 80,
				requires: {
					selection: true
				},
				click: function() {
					this.controller.state().trigger( 'insert', selection );
					this.controller.close();
				}
			}
		});

		wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
	}
});

module.exports = VenueSelectToolbar;
