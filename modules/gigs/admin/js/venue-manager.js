/*jshint browserify:true */
/*global _audiothemeVenueManagerSettings:false */

'use strict';

var settings,
	app = require( 'audiotheme' );

settings = app.settings( _audiothemeVenueManagerSettings );

app.model.Venue = require( './models/venue' );
app.model.Venues = require( './models/venues' );
app.model.VenuesQuery = require( './models/venues-query' );

app.controller.Venues = require( './controllers/venues' );
app.controller.VenueAdd = require( './controllers/venue-add' );

app.view.Frame = require( './views/frame' );
app.view.VenueFrame = require( './views/frame/venue' );
app.view.VenuesContent = require( './views/content/venues' );
app.view.VenueAddContent = require( './views/content/venue-add' );
app.view.VenuesSearch = require( './views/venues-search' );
app.view.VenuesList = require( './views/venues-list' );
app.view.VenuesListItem = require( './views/venues-list-item' );
app.view.VenuePanel = require( './views/venue-panel' );
app.view.VenuePanelTitle = require( './views/venue-panel-title' );
app.view.VenueDetails = require( './views/venue-details' );
app.view.VenueAddForm = require( './views/venue-add-form' );
app.view.VenueEditForm = require( './views/venue-edit-form' );
app.view.VenueAddToolbar = require( './views/toolbar/venue-add' );
app.view.VenueSelectToolbar = require( './views/toolbar/venue-select' );
