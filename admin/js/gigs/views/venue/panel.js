/* jshint browserify: true */

'use strict';

var VenuePanel,
	VenueDetails = require( './details' ),
	VenueEditForm = require( './edit-form' ),
	VenuePanelTitle = require( './panel-title' ),
	wp = require( 'wp' );

VenuePanel = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venue-panel',

	initialize: function() {
		this.listenTo( this.controller.state().get( 'selection' ), 'reset', this.render );
		this.listenTo( this.controller.state(), 'change:mode', this.render );
	},

	render: function() {
		var panelContent,
			model = this.controller.state().get( 'selection' ).first();

		if ( ! this.controller.state( 'venues' ).get( 'selection' ).length ) {
			return this;
		}

		if ( 'edit' === this.controller.state().get( 'mode' ) ) {
			panelContent = new VenueEditForm({
				controller: this.controller,
				model: model
			});
		} else {
			panelContent = new VenueDetails({
				controller: this.controller,
				model: model
			});
		}

		this.views.set([
			new VenuePanelTitle({
				controller: this.controller,
				model: model
			}),
			panelContent
		]);

		return this;
	}
});

module.exports = VenuePanel;
