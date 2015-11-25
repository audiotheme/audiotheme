/*jshint browserify:true */
/*global _audiothemeVenueManagerSettings:false */

'use strict';

var settings,
	app = require( 'audiotheme' );

settings = app.settings( _audiothemeVenueManagerSettings );

app.model.Venue = require( './gigs/models/venue' );
app.model.Venues = require( './gigs/models/venues' );
app.model.VenuesQuery = require( './gigs/models/venues-query' );

app.controller.Venues = require( './gigs/controllers/venues' );
app.controller.VenueAdd = require( './gigs/controllers/venue-add' );

app.view.Frame = require( './gigs/views/frame' );
app.view.VenueFrame = require( './gigs/views/frame/venue' );
app.view.VenuesContent = require( './gigs/views/content/venues' );
app.view.VenueAddContent = require( './gigs/views/content/venue-add' );
app.view.VenuesSearch = require( './gigs/views/venues-search' );
app.view.VenuesList = require( './gigs/views/venues-list' );
app.view.VenuesListItem = require( './gigs/views/venues-list-item' );
app.view.VenuePanel = require( './gigs/views/venue-panel' );
app.view.VenuePanelTitle = require( './gigs/views/venue-panel-title' );
app.view.VenueDetails = require( './gigs/views/venue-details' );
app.view.VenueAddForm = require( './gigs/views/venue-add-form' );
app.view.VenueEditForm = require( './gigs/views/venue-edit-form' );
app.view.VenueAddToolbar = require( './gigs/views/toolbar/venue-add' );
app.view.VenueSelectToolbar = require( './gigs/views/toolbar/venue-select' );
