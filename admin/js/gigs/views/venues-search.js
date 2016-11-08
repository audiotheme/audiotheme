/*jshint browserify:true */

'use strict';

var VenuesSearch,
	wp = require( 'wp' );

VenuesSearch = wp.media.View.extend({
	tagName: 'div',
	className: 'audiotheme-venues-search',
	template: wp.template( 'audiotheme-venues-search-field' ),

	events: {
		'keyup input': 'search',
		'search input': 'search'
	},

	render: function() {
		this.$field = this.$el.html( this.template() ).find( 'input' );
		return this;
	},

	search: function() {
		var view = this;

		clearTimeout( this.timeout );
		this.timeout = setTimeout(function() {
			view.controller.state().search( view.$field.val() );
		}, 300 );
	}
});

module.exports = VenuesSearch;
