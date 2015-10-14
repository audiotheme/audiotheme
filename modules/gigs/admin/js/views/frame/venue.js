var VenueFrame,
	_ = require( 'underscore' ),
	Frame = require( '../frame' ),
	settings = require( 'audiotheme' ).settings(),
	VenueAddContent = require( '../content/venue-add' ),
	VenueAddController = require( '../../controllers/venue-add' ),
	VenueAddToolbar = require( '../toolbar/venue-add' ),
	VenueSelectToolbar = require( '../toolbar/venue-select' ),
	VenuesContent = require( '../content/venues' ),
	VenuesController = require( '../../controllers/venues' );

VenueFrame = Frame.extend({
	className: 'media-frame audiotheme-venue-frame',

	initialize: function() {
		Frame.prototype.initialize.apply( this, arguments );

		_.defaults( this.options, {
			title: '',
			modal: true,
			state: 'audiotheme-venues'
		});

		this.createStates();
		this.bindHandlers();
	},

	createStates: function() {
		this.states.add( new VenuesController() );

		if ( settings.canPublishVenues ) {
			this.states.add( new VenueAddController() );
		}
	},

	bindHandlers: function() {
		this.on( 'content:create:audiotheme-venues', this.createContent, this );
		this.on( 'toolbar:create:main-audiotheme-venues', this.createSelectToolbar, this );
		this.on( 'toolbar:create:audiotheme-venue-add', this.createAddToolbar, this );
		this.on( 'content:render:audiotheme-venue-add', this.renderAddContent, this );
	},

	createContent: function( contentRegion ) {
		contentRegion.view = new VenuesContent({
			controller: this,
			collection: this.state().get( 'venues' ),
			searchQuery: this.state().get( 'search' )
		});
	},

	createSelectToolbar: function( toolbar ) {
		toolbar.view = new VenueSelectToolbar({
			controller: this
		});
	},

	createAddToolbar: function( toolbar ) {
		toolbar.view = new VenueAddToolbar({
			controller: this,
			model: this.state( 'audiotheme-venue-add' ).get( 'model' )
		});
	},

	renderAddContent: function() {
		this.content.set( new VenueAddContent({
			controller: this
		}) );
	}
});

module.exports = VenueFrame;
