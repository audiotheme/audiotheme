/*global _:false, _audiothemePlaylistSettings:false, Backbone:false, cue:false, wp:false */

window.cue = window.cue || {};

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var l10n = _audiothemePlaylistSettings.l10n,
		frame;


	/**
	 * ========================================================================
	 * MODELS
	 * ========================================================================
	 */

	/**
	 * Fetch a track by id.
	 */
	cue.model.Track.prototype.fetch = function() {
		// @todo Make sure the track has an id.
		return wp.ajax.post( 'audiotheme_ajax_get_playlist_track', {
			post_id: this.get( 'id' )
		});
	};

	/**
	 * Add one or more track CPTs to the collection.
	 *
	 * @param int|array ids One or more track CPT IDs.
	 */
	cue.model.Tracks.prototype.addTracks = function( ids ) {
		var collection = this;

		return wp.ajax.post( 'audiotheme_ajax_get_playlist_tracks', {
			post__in: ids
		}).done(function( tracks ) {
			collection.add( tracks );
		});
	};


	/**
	 * ========================================================================
	 * CONTROLLERS
	 * ========================================================================
	 */

	/**
	 * cue.controller.AudiothemePlaylistTracks
	 */
	cue.controller.AudiothemePlaylistTracks = wp.media.controller.State.extend({
		defaults: {
			id:      'audiotheme-playlist-tracks',
			menu:    'default',
			content: 'audiotheme-playlist-tracks',
			toolbar: 'main-audiotheme-playlist-tracks',
			title:   l10n.frameTitle,
			button:  { text: l10n.frameButtonText },
			menuItem: { text: l10n.frameMenuItemText, priority: 100 }
		},

		initialize: function() {
			this.set( 'records', new Backbone.Collection() );
			this.set( 'selection', new Backbone.Collection() );
		}
	});


	/**
	 * ========================================================================
	 * VIEWS
	 * ========================================================================
	 */

	/**
	 * cue.view.AudiothemePlaylistTracksContent
	 */
	cue.view.AudiothemePlaylistTracksContent = wp.media.View.extend({
		className: 'media-audiotheme-playlist-tracks',

		initialize: function() {
			this.listenTo( this.collection, 'reset', this.render );
		},

		render: function() {
			var selection = this.controller.state().get( 'selection' ).pluck( 'id' );

			if ( ! this.collection.length ) {
				this.getRecords();
			}

			this.$el.html( '<ul />' );

			this.collection.each(function( record ) {
				var recordView = new cue.view.AudiothemePlaylistRecord({
					controller: this.controller,
					model: record
				}).render();

				this.$el.children( 'ul' ).append( recordView.el );
			}, this );

			return this;
		},

		getRecords: function() {
			var view = this;
			wp.ajax.post( 'audiotheme_ajax_get_playlist_records').done(function( records ) {
				view.collection.reset( records );
			});
		}
	});

	/**
	 * cue.view.AudiothemePlaylistTracksToolbar
	 */
	cue.view.AudiothemePlaylistTracksToolbar = wp.media.view.Toolbar.extend({
		initialize: function() {
			var options = this.options,
				controller = this.controller,
				state = controller.state();

			// This is a button.
			options.items = _.defaults( options.items || {}, {
				select: {
					text: state.get( 'button' ).text,
					style: 'primary',
					priority: 80,
					requires: {
						selection: true
					},
					click: function() {
						var selection = state.get( 'selection' ),
							trackIds = selection.pluck( 'id' );

						cue.tracks.addTracks( trackIds );

						controller.close();
						state.trigger( 'select', trackIds );

						// Restore and reset the default state.
						controller.setState( controller.options.state );
						controller.reset();
						selection.reset();
					}
				}
			});

			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
		}
	});

	/**
	 * cue.view.AudiothemePlaylistRecord
	 */
	cue.view.AudiothemePlaylistRecord = wp.media.View.extend({
		tagName: 'li',
		className: 'audiotheme-playlist-record',
		template: wp.template( 'audiotheme-playlist-record' ),

		events: {
			'click .audiotheme-playlist-record-track': 'toggleSelection'
		},

		render: function() {
			this.$el.html( this.template( this.model.toJSON() ) );
			this.refreshSelected();
			return this;
		},

		refreshSelected: function() {
			var view = this,
				selection = this.controller.state().get( 'selection' ).pluck( 'id' ),
				tracks = this.model.get( 'tracks' );

			_.each( tracks, function( track ) {
				var $track = view.$el.find( '.audiotheme-playlist-record-track[data-id="' + track.id + '"]' );

				// Select tracks that are in the selection.
				$track.toggleClass( 'is-selected', -1 !== _.indexOf( selection, parseInt( track.id, 10 ) ) );
			});
		},

		toggleSelection: function( e ) {
			var $track = $( e.target ).closest( '.audiotheme-playlist-record-track' ),
				trackId = $track.data( 'id' ),
				selection = this.controller.state().get( 'selection' );

			$track.toggleClass( 'is-selected' );

			if ( $track.hasClass( 'is-selected' ) ) {
				selection.add({ id: trackId });
			} else {
				selection.remove( trackId );
			}
		}
	});


	/**
	 * ========================================================================
	 * SETUP
	 * ========================================================================
	 */

	// Initialize the frame.
	frame = cue.workflows.get( 'addTracks' );

	// Add a new state.
	frame.states.add([
		new cue.controller.AudiothemePlaylistTracks()
	]);

	// Set the content view.
	frame.on( 'content:render:audiotheme-playlist-tracks', _.bind( function( view ) {
		var view = new cue.view.AudiothemePlaylistTracksContent({
			controller: this,
			mode: this.state(),
			collection: this.state().get( 'records' ),
		});

		this.content.set( view );
	}, frame ) );

	// Set the toolbar view.
	frame.on( 'toolbar:create:main-audiotheme-playlist-tracks', _.bind( function( toolbar ) {
		toolbar.view = new cue.view.AudiothemePlaylistTracksToolbar({
			controller: this
		});
	}, frame ) );

})( this, jQuery, _, Backbone, wp );
