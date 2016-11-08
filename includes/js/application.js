/*jshint browserify:true */

'use strict';

var _ = require( 'underscore' );

function Application() {
	var settings = {};

	_.extend( this, {
		controller: {},
		l10n: {},
		model: {},
		view: {}
	});

	this.settings = function( options ) {
		if ( options ) {
			_.extend( settings, options );
		}

		if ( settings.l10n ) {
			this.l10n = _.extend( this.l10n, settings.l10n );
			delete settings.l10n;
		}

		return settings || {};
	};
}

global.audiotheme = global.audiotheme || new Application();
module.exports = global.audiotheme;
