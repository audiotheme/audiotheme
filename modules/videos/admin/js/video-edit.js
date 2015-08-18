/*global _:false, _audiothemeVideoThumbnailPing:true, Backbone:false, wp:false, WPSetThumbnailHTML:false, WPSetThumbnailID:false */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var theVideo,
		app = {};

	_.extend( app, { model: {}, view: {} } );

	window._audiothemeVideoThumbnailPing = function() {
		var data = JSON.parse( $( '#postimagediv' ).find( '#audiotheme-video-thumbnail-data' ).html() );

		if ( theVideo ) {
			theVideo.set( 'thumbnailId', data.thumbnailId );
			theVideo.set( 'oembedThumbnailId', data.oembedThumbnailId );
		}
	};

	app.model.Video = Backbone.Model.extend({
		defaults: {
			title: '',
			videoUrl: '',
			thumbnailId: '',
			oembedThumbnailId: ''
		},

		getEmbedHtml: function() {
			return wp.ajax.post( 'parse-embed', {
				post_ID: this.get( 'id' ),
				shortcode: '[embed]' + this.get( 'videoUrl' ) + '[/embed]'
			});
		},

		getRemoteThumbnail: function() {
			return wp.ajax.post( 'audiotheme_get_video_oembed_data', {
				post_id: this.get( 'id' ),
				video_url: this.get( 'videoUrl' )
			});
		}
	});

	app.view.PostForm = wp.Backbone.View.extend({
		el: '#post',

		events: {
			'change #audiotheme-video-url': 'updateVideoUrl',
		},

		initialize: function() {
			this.render();
		},

		render: function() {
			new app.view.MetaBoxThumbnail({
				model: this.model
			});

			this.views.add( '.audiotheme-edit-after-title', [
				new app.view.Preview({
					model: this.model
				}),
			]);

			return this;
		},

		updateVideoUrl: function() {
			this.model.set( 'videoUrl', this.$el.find( '#audiotheme-video-url' ).val() );
		}
	});

	app.view.Preview = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'audiotheme-video-preview',

		initialize: function() {
			this.listenTo( this.model, 'change:videoUrl', this.render );
		},

		render: function() {
			var self = this;
			this.model.getEmbedHtml().done(function( response ) {
				self.$el.html( response.body );

				if ( $.isFunction( $.fn.fitVids ) ) {
					self.$el.fitVids();
				}
			}).fail(function() {
				self.$el.html( '' );
			});
			return this;
		}
	});

	app.view.MetaBoxThumbnail = wp.Backbone.View.extend({
		el: '#postimagediv',

		events: {
			'click #audiotheme-select-oembed-thumb-button': 'getRemoteThumbnail'
		},

		initialize: function() {
			this.toggleLink();
			this.listenTo( this.model, 'change:videoUrl change:thumbnailId change:oembedThumbnailId', this.toggleLink );
		},

		getRemoteThumbnail: function( e ) {
			var $spinner = this.$el.find( '#audiotheme-select-oembed-thumb .spinner' ).css({
				display: 'inline-block',
				visibility: 'visible'
			});

			e.preventDefault();

			this.model.getRemoteThumbnail()
				.always(function() {
					$spinner.hide();
				})
				.done(function( response ) {
					WPSetThumbnailID( response.thumbnailId );
					WPSetThumbnailHTML( response.thumbnailMetaBoxHtml );
				});
		},

		toggleLink: function() {
			var $el = $( '#audiotheme-select-oembed-thumb' ),
				thumbnailId = this.model.get( 'thumbnailId' ),
				oembedThumbnailId = this.model.get( 'oembedThumbnailId' );

			if (
				'' === this.model.get( 'videoUrl' ) ||
				( '' !== thumbnailId && thumbnailId === oembedThumbnailId )
			) {
				$el.hide();
			} else {
				$el.show();
			}
		}
	});

	$( document ).ready(function() {
		theVideo = new app.model.Video({
			id: parseInt( $( '#post_ID' ).val(), 10 ),
			title: $( '#title' ).val(),
			videoUrl: $( '#audiotheme-video-url' ).val(),
		});

		new app.view.PostForm({
			model: theVideo
		});

		_audiothemeVideoThumbnailPing();
	});

})( window, jQuery, _, Backbone, wp );
