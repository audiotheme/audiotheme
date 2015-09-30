var VenuesController,
	l10n = require( 'audiotheme' ).l10n,
	Venues = require( '../models/venues' ),
	VenuesQuery = require( '../models/venues-query' ),
	wp = require( 'wp' );

VenuesController = wp.media.controller.State.extend({
	defaults: {
		id:      'audiotheme-venues',
		menu:    'audiotheme-venues',
		content: 'audiotheme-venues',
		toolbar: 'main-audiotheme-venues',
		title:   l10n.venues || 'Venues',
		button:  {
			text: l10n.select || 'Select'
		},
		menuItem: {
			text: l10n.manageVenues || 'Manage Venues',
			priority: 10
		},
		mode: 'view',
		provider: 'venues'
	},

	initialize: function() {
		var search = new VenuesQuery({}, { props: { s: '' } }),
			venues = new VenuesQuery();

		this.set( 'search', search );
		this.set( 'venues', venues );
		this.set( 'selection', new Venues() );

		// Synchronize changes to models in each collection.
		search.observe( venues );
		venues.observe( search );
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
