/*jshint browserify:true */

'use strict';

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
			state: 'venues'
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
		this.on( 'content:create:venues-manager', this.createContent, this );
		this.on( 'toolbar:create:venues', this.createSelectToolbar, this );
		this.on( 'toolbar:create:venue-add', this.createAddToolbar, this );
		this.on( 'content:render:venue-add', this.renderAddContent, this );
	},

	createContent: function( contentRegion ) {
		contentRegion.view = new VenuesContent({
			controller: this,
			collection: this.state().get( 'venues' ),
			searchQuery: this.state().get( 'search' ),
			selection: this.state().get( 'selection' )
		});
	},

	createSelectToolbar: function( toolbar ) {
		toolbar.view = new VenueSelectToolbar({
			controller: this,
			selection: this.state().get( 'selection' )
		});
	},

	createAddToolbar: function( toolbar ) {
		toolbar.view = new VenueAddToolbar({
			controller: this,
			model: this.state( 'venue-add' ).get( 'model' )
		});
	},

	renderAddContent: function() {
		this.content.set( new VenueAddContent({
			model: this.state( 'venue-add' ).get( 'model' )
		}) );
	}
});

module.exports = VenueFrame;
