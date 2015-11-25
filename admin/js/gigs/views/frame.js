/*jshint browserify:true */

'use strict';

var Frame,
	_ = require( 'underscore' ),
	wp = require( 'wp' );

Frame = wp.media.view.Frame.extend({
	className: 'media-frame',
	template: wp.media.template( 'media-frame' ),
	regions: [ 'menu', 'title', 'content', 'toolbar' ],

	initialize: function() {
		wp.media.view.Frame.prototype.initialize.apply( this, arguments );

		_.defaults( this.options, {
			title: '',
			modal: true
		});

		// Ensure core UI is enabled.
		this.$el.addClass( 'wp-core-ui' );

		// Initialize modal container view.
		if ( this.options.modal ) {
			this.modal = new wp.media.view.Modal({
				controller: this,
				title:      this.options.title
			});

			this.modal.content( this );
		}

		this.on( 'attach', _.bind( this.views.ready, this.views ), this );

		// Bind default title creation.
		this.on( 'title:create:default', this.createTitle, this );
		this.title.mode( 'default' );

		this.on( 'menu:create:audiotheme-venues', this.createMenu, this );
	},

	render: function() {
		// Activate the default state if no active state exists.
		if ( ! this.state() && this.options.state ) {
			this.setState( this.options.state );
		}

		// Call 'render' directly on the parent class.
		return wp.media.view.Frame.prototype.render.apply( this, arguments );
	},

	createTitle: function( title ) {
		title.view = new wp.media.View({
			controller: this,
			tagName: 'h1'
		});
	},

	createMenu: function( menu ) {
		menu.view = new wp.media.view.Menu({
			controller: this
		});
	},

	createToolbar: function( toolbar ) {
		toolbar.view = new wp.media.view.Toolbar({
			controller: this
		});
	}
});

// Map some of the modal's methods to the frame.
_.each([ 'open', 'close', 'attach', 'detach', 'escape' ], function( method ) {
	/**
	 * @returns {wp.media.view.VenueFrame} Returns itself to allow chaining.
	 */
	Frame.prototype[ method ] = function() {
		if ( this.modal ) {
			this.modal[ method ].apply( this.modal, arguments );
		}
		return this;
	};
});

module.exports = Frame;
