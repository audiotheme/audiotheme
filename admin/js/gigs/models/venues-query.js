/*jshint browserify:true */

'use strict';

var VenuesQuery,
	_ = require( 'underscore' ),
	Backbone = require( 'backbone' ),
	Venues = require( './venues' ),
	wp = require( 'wp' );

VenuesQuery = Venues.extend({
	initialize: function( models, options ) {
		options = options || {};
		Venues.prototype.initialize.apply( this, arguments );

		this.props = new Backbone.Model();
		this.props.set( _.defaults( options.props || {} ) );
		this.props.on( 'change', this.requery, this );

		this.args = _.extend( {}, {
			posts_per_page: 20
		}, options.args || {} );

		this._hasMore = true;
	},

	hasMore: function() {
		return this._hasMore;
	},

	/**
	 * Fetch more venues from the server for the collection.
	 *
	 * @param   {object}  [options={}]
	 * @returns {Promise}
	 */
	more: function( options ) {
		var query = this;

		// If there is already a request pending, return early with the Deferred object.
		if ( this._more && 'pending' === this._more.state() ) {
			return this._more;
		}

		if ( ! this.hasMore() ) {
			return Backbone.$.Deferred().resolveWith( this ).promise();
		}

		options = options || {};
		options.remove = false;

		return this._more = this.fetch( options ).done(function( response ) {
			if ( _.isEmpty( response ) || -1 === this.args.posts_per_page || response.length < this.args.posts_per_page ) {
				query._hasMore = false;
			}
		});
	},

	observe: function( collection ) {
		var self = this;

		collection.on( 'change', function( model ) {
			self.set( model, { add: false, remove: false });
		});
	},

	requery: function() {
		this._hasMore = true;
		this.args.paged = 1;
		this.fetch({ reset: true });
	},

	/**
	 * Overrides Backbone.Collection.sync
	 *
	 * @param {String} method
	 * @param {Backbone.Model} model
	 * @param {Object} [options={}]
	 * @returns {Promise}
	 */
	sync: function( method, model, options ) {
		var args, fallback;

		// Overload the read method so VenuesQuery.fetch() functions correctly.
		if ( 'read' === method ) {
			options = options || {};
			options.context = this;

			options.data = _.extend( options.data || {}, {
				action: 'audiotheme_ajax_get_venues'
			});

			args = _.clone( this.args );

			if ( this.props.get( 's' ) ) {
				args.s = this.props.get( 's' );
			}

			// Determine which page to query.
			if ( -1 !== args.posts_per_page ) {
				args.paged = Math.floor( this.length / args.posts_per_page ) + 1;
			}

			options.data.query_args = args;
			return wp.ajax.send( options );
		}

		// Otherwise, fall back to Backbone.sync()
		else {
			fallback = Venues.prototype.sync ? Venues.prototype : Backbone;
			return fallback.sync.apply( this, arguments );
		}
	}
});

module.exports = VenuesQuery;
