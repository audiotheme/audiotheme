/*jshint browserify:true */

'use strict';

var VenueSelectToolbar,
	_ = require( 'underscore' ),
	wp = require( 'wp' );

VenueSelectToolbar = wp.media.view.Toolbar.extend({
	initialize: function( options ) {
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
					var state = this.controller.state(),
						selection = state.get( 'selection' );

					state.trigger( 'insert', selection );
					this.controller.close();
				}
			}
		});

		wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
	}
});

module.exports = VenueSelectToolbar;
