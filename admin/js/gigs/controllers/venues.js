/* jshint browserify: true */

'use strict';

var VenuesController,
	Backbone = require( 'backbone' ),
	l10n = require( 'audiotheme' ).l10n,
	Venues = require( '../models/venues' ),
	VenuesQuery = require( '../models/venues-query' ),
	wp = require( 'wp' );

VenuesController = wp.media.controller.State.extend({
	defaults: {
		id:      'venues',
		title:   l10n.venues || 'Venues',
		button:  {
			text: l10n.select || 'Select'
		},
		content: 'venues-manager',
		menu:    'default',
		menuItem: {
			text: l10n.manageVenues || 'Manage Venues',
			priority: 10
		},
		mode: 'view',
		toolbar: 'venues',
		provider: 'venues'
	},

	initialize: function() {
		var search = new VenuesQuery({}, { props: { s: '' } }),
			venues = new VenuesQuery();

		this.set( 'search', search );
		this.set( 'venues', venues );
		this.set( 'selection', new Venues() );
		this.set( 'selectedItem', Backbone.$() );

		// Synchronize changes to models in each collection.
		search.observe( venues );
		venues.observe( search );
	},

	next: function() {
		var provider = this.get( 'provider' ),
			collection = this.get( provider ),
			currentIndex = collection.indexOf( this.get( 'selection' ).at( 0 ) );

		if ( collection.length - 1 !== currentIndex ) {
			this.get( 'selection' ).reset( collection.at( currentIndex + 1 ) );
		}
	},

	previous: function() {
		var provider = this.get( 'provider' ),
			collection = this.get( provider ),
			currentIndex = collection.indexOf( this.get( 'selection' ).at( 0 ) );

		if ( 0 !== currentIndex ) {
			this.get( 'selection' ).reset( collection.at( currentIndex - 1 ) );
		}
	},

	search: function( query ) {
		// Restore the original state if the text in the search field
		// is less than 3 characters.
		if ( query.length < 3 ) {
			this.get( 'search' ).reset();
			this.set( 'provider', 'venues' );
			return;
		}

		this.set( 'provider', 'search' );
		this.get( 'search' ).props.set( 's', query );
	}
});

module.exports = VenuesController;
