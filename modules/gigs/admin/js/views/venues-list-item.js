/*jshint browserify:true */

'use strict';

var VenuesListItem,
	wp = require( 'wp' );

VenuesListItem = wp.media.View.extend({
	tagName: 'li',
	className: 'audiotheme-venues-list-item',

	events: {
		'click': 'setSelection'
	},

	initialize: function() {
		var selection = this.controller.state( 'audiotheme-venues' ).get( 'selection' );
		selection.on( 'reset', this.updateSelected, this );
		this.listenTo( this.model, 'change:name', this.render );
	},

	render: function() {
		this.$el.html( this.model.get( 'name' ) );
		this.updateSelected();
		return this;
	},

	setSelection: function() {
		this.controller.state().get( 'selection' ).reset( this.model );
	},

	updateSelected: function() {
		var state = this.controller.state( 'audiotheme-venues' ),
			isSelected =state.get( 'selection' ).first() === this.model;

		this.$el.toggleClass( 'is-selected', isSelected );

		if ( isSelected ) {
			state.set( 'selectedItem', this.$el );
		}
	}
});

module.exports = VenuesListItem;
