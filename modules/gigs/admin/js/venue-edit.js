/*jshint browserify:true */

'use strict';

var $ = require( 'jquery' );

require( './gigs/utils/city-typeahead' )(
	$( '#venue-city' ),
	$( '#venue-state' ),
	$( '#venue-country' ),
	$( '#venue-timezone-string' )
);
