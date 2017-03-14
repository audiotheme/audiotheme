/* jshint browserify: true */

'use strict';

var VenuePanelTitle,
	l10n = require( 'audiotheme' ).l10n,
	wp = require( 'wp' );

/**
 *
 *
 * @todo Don't show the button if the user can't edit venues.
 */
VenuePanelTitle = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venue-panel-title',
	template: wp.template( 'audiotheme-venue-panel-title' ),

	events: {
		'click button': 'toggleMode'
	},

	initialize: function( options ) {
		this.model = options.model;
		this.listenTo( this.model, 'change:name', this.updateTitle );
	},

	render: function() {
		var state = this.controller.state( 'venues' ),
			mode = state.get( 'mode' );

		this.$el.html( this.template( this.model.toJSON() ) );
		this.$el.find( 'button' ).text( 'edit' === mode ? l10n.view || 'View' : l10n.edit || 'Edit' );
		return this;
	},

	toggleMode: function( e ) {
		var mode = this.controller.state().get( 'mode' );
		e.preventDefault();
		this.controller.state().set( 'mode', 'edit' === mode ? 'view' : 'edit' );
	},

	updateTitle: function() {
		this.$el.find( 'h2' ).text( this.model.get( 'name' ) );
	}
});

module.exports = VenuePanelTitle;
