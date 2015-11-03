var VenuesList,
	_ = require( 'underscore' ),
	VenuesListItem = require( './venues-list-item' ),
	wp = require( 'wp' );

/**
 *
 *
 * @todo Show feedback (spinner) when searching.
 */
VenuesList = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venues',

	initialize: function( options ) {
		var state = this.controller.state();

		this.listenTo( state, 'change:provider', this.switchCollection );
		this.listenTo( this.collection, 'add', this.addVenue );
		this.listenTo( this.collection, 'reset', this.render );
		this.listenTo( state.get( 'search' ), 'reset', this.render );
		this.listenTo( state, 'change:selectedItem', this.maybeMakeItemVisible );
	},

	render: function() {
		this.$el
			.off( 'scroll' )
			.on( 'scroll', _.bind( this.scroll, this ) )
			.html( '<ul />' );

		if ( this.collection.length ) {
			this.collection.each( this.addVenue, this );
		} else {
			// @todo Show feedback about there not being any matches.
		}
		return this;
	},

	addVenue: function( venue ) {
		var view = new VenuesListItem({
			controller: this.controller,
			model: venue
		}).render();

		this.$el.children( 'ul' ).append( view.el );
	},

	maybeMakeItemVisible: function() {
		var $item = this.controller.state().get( 'selectedItem' ),
			itemHeight = $item.outerHeight(),
			itemTop = $item.position().top;

		if ( itemTop > this.el.clientHeight + this.el.scrollTop - itemHeight ) {
			this.el.scrollTop = itemTop - this.el.clientHeight + itemHeight;
		} else if ( itemTop < this.el.scrollTop ) {
			this.el.scrollTop = itemTop;
		}
	},

	scroll: function() {
		if ( this.el.scrollHeight < this.el.scrollTop + this.el.clientHeight * 3 && this.collection.hasMore() ) {
			this.collection.more();
		}
	},

	switchCollection: function() {
		var state = this.controller.state(),
			provider = state.get( 'provider' );

		this.collection = state.get( provider );
		this.render();
	}
});

module.exports = VenuesList;
