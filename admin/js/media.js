/*global AudiothemeMediaControl:false, jQuery:false, wp:false */

(function( $, window, undefined ) {
	'use strict';

	/**
	 * Media control frame popup.
	 *
	 * This script listens for a click on an element with a
	 * 'audiotheme-media-control-choose' class residing within an element with
	 * class of 'audiotheme-media-control'. When the click is detected it looks for
	 * custom data attributes to modify the behavior of the media manager.
	 */
	jQuery(function( $ ) {
		var Attachment = wp.media.model.Attachment,
			defaultExtensions = wp.Uploader.defaults.filters.mime_types || [],
			$control, $controlTarget, mediaControl;

		mediaControl = {
			init: function() {
				$( '#wpbody' ).on( 'click', '.audiotheme-media-control-choose', function( e ) {
					var targetSelector;

					e.preventDefault();

					$control = $( this ).closest( '.audiotheme-media-control' );

					targetSelector = $control.data( 'target' ) || '.audiotheme-media-control-target';
					if ( 0 === targetSelector.indexOf( '#' ) ) {
						// Context doesn't matter if the selector is an ID.
						$controlTarget = $( targetSelector );
					} else {
						// Search for other selectors within the context of the control.
						$controlTarget = $control.find( targetSelector );
					}

					if ( $control.data( 'upload-extensions' ) ) {
						wp.Uploader.defaults.filters.mime_types = [ {
							title: AudiothemeMediaControl.audioFiles,
							extensions: $control.data( 'upload-extensions' )
						} ];
					}

					mediaControl.frame().open();
				});
			},

			// Updates the control when an image is selected from the media library.
			select: function() {
				var selection = this.get( 'selection' ),
					returnProperty = $control.data( 'return-property' ) || 'id';

				// Insert the selected attachment ids into the target element.
				if ( $controlTarget.length ) {
					$controlTarget.val( selection.pluck( returnProperty ) );
				}

				// Trigger an event on the control to allow custom updates.
				$control.trigger( 'selectionChange.audiotheme', [ selection ]);
			},

			// Updates the selected image in the media library based on the image in the control.
			updateLibrarySelection: function() {
				var selection = this.get( 'library' ).get( 'selection' ),
					attachment, selectedIds;

				if ( $controlTarget.length ) {
					selectedIds = $controlTarget.val();
					if ( selectedIds && '' !== selectedIds && -1 !== selectedIds && '0' !== selectedIds ) {
						attachment = Attachment.get( selectedIds );
						attachment.fetch();
					}
				}

				selection.reset( attachment ? [ attachment ] : [] );
			},

			// Initializes a new media manage or returns an existing frame.
			// @see wp.media.featuredImage.frame()
			frame: function() {
				if ( this._frame ) {
					return this._frame;
				}

				this._frame = wp.media({
					title: $control.data( 'title' ) || AudiothemeMediaControl.frameTitle,
					library: {
						type: $control.data( 'file-type' ) || 'image'
					},
					button: {
						text: $control.data( 'update-text' ) || AudiothemeMediaControl.frameUpdateText
					},
					multiple: $control.data( 'select-multiple' ) || false
				});

				this._frame.on( 'open', this.updateLibrarySelection ).on( 'close', function() {
					wp.Uploader.defaults.filters.mime_types = defaultExtensions;
				}).state( 'library' ).on( 'select', this.select );

				return this._frame;
			}
		};

		mediaControl.init();
	});

})( jQuery, window );
