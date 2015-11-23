/*jshint browserify:true */
/*global _audiothemeAdminSettings */

'use strict';

var $ = require( 'jquery' ),
	app = require( 'audiotheme' ),
	wp = require( 'wp' );

app.settings( _audiothemeAdminSettings );

$(function( $ ) {
	$( '.wrap' ).on( 'focus', '.audiotheme-input-group input', function() {
		$( this ).parent().addClass( 'is-focused' );
	}).on( 'blur', '.audiotheme-input-group input', function() {
		$( this ).parent().removeClass( 'is-focused' );
	});
});

$(function( $ ) {
	$( '.audiotheme-taxonomy-meta-box' ).each(function() {
		var $this = $( this ),
			$group = $this.find( '.audiotheme-add-term-group' ),
			$button = $group.find( '.button' ).attr( 'disabled', false ),
			$field = $group.find( '.audiotheme-add-term-field' ),
			$list = $this.find( '.audiotheme-taxonomy-term-list ul' ),
			$response = $( '.audiotheme-add-term-response' );

		$field.on( 'keypress', function( e ) {
			if ( 13 === e.which ) {
				e.preventDefault();
				$button.click();
			}
		});

		// Add a record type.
		$button.on( 'click', function() {
			$group.addClass( 'is-loading' );
			$button.attr( 'disabled', true );
			$response.text( '' ).hide();

			wp.ajax.post( 'audiotheme_ajax_insert_term', {
				taxonomy: $this.data( 'taxonomy' ),
				term: $field.val(),
				nonce: $group.find( '.audiotheme-add-term-nonce' ).val()
			}).done(function( response ) {
				$field.val( '' );
				$list.prepend( response.html );
			}).fail(function( response ) {
				$response.css( 'display', 'block' ).text( response.message );
			}).always(function() {
				$group.removeClass( 'is-loading' );
				$button.attr( 'disabled', false );
			});
		});
	});
});

/**
 * Repeater
 */
// .audiotheme-clear-on-add will clear the value of a form element in a newly added row.
// .audiotheme-hide-on-add will hide the element in a newly added row.
// .audiotheme-remove-on-add will remove an element from a newly added row.
// .audiotheme-show-on-add will show a hidden elment in a newly added row.

var methods = {
	init: function( options ) {
		var settings = {
			items: null
		};

		if ( options ) {
			$.extend( settings, options );
		}

		return this.each(function() {
			var repeater = $( this ),
				itemsParent = repeater.find( '.audiotheme-repeater-items' ),
				itemTemplate, template;

			if ( repeater.data( 'item-template-id' ) ) {
				template = wp.template( repeater.data( 'item-template-id' ) );

				if ( settings.items ) {
					repeater.audiothemeRepeater( 'clearList' );

					$.each( settings.items, function( i, item ) {
						itemsParent.append( template( item ).replace( /__i__/g, i ) );
					});
				}

				itemTemplate = template({});
				itemTemplate = $( itemTemplate.replace( /__i__/g, '0' ) );
			} else {
				itemTemplate = repeater.find( '.audiotheme-repeater-item:eq(0)' ).clone();
			}

			repeater.data( 'itemIndex', repeater.find( '.audiotheme-repeater-item' ).length || 0 );
			repeater.data( 'itemTemplate', itemTemplate );

			repeater.audiothemeRepeater( 'updateIndex' );

			itemsParent.sortable({
				axis: 'y',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				helper: function( e, ui ) {
					var $helper = ui.clone();
					$helper.children().each(function( index ) {
						$( this ).width( ui.children().eq( index ).width() );
					});

					return $helper;
				},
				update: function() {
					repeater.audiothemeRepeater( 'updateIndex' );
				},
				change: function() {
					repeater.find( '.audiotheme-repeater-sort-warning' ).fadeIn( 'slow' );
				}
			});

			repeater.find( '.audiotheme-repeater-add-item' ).on( 'click', function( e ) {
				e.preventDefault();
				$( this ).closest( '.audiotheme-repeater' ).audiothemeRepeater( 'addItem' );
			});

			repeater.on( 'click', '.audiotheme-repeater-remove-item', function( e ) {
				var repeater = $( this ).closest( '.audiotheme-repeater' );
				e.preventDefault();
				$( this ).closest( '.audiotheme-repeater-item' ).remove();
				repeater.audiothemeRepeater( 'updateIndex' );
			});

			repeater.on( 'blur', 'input,select,textarea', function() {
				$( this ).closest( '.audiotheme-repeater' ).find( '.audiotheme-repeater-item' ).removeClass( 'audiotheme-repeater-active-item' );
			}).on( 'focus', 'input,select,textarea', function() {
				$( this ).closest( '.audiotheme-repeater-item' ).addClass( 'audiotheme-repeater-active-item' ).siblings().removeClass( 'audiotheme-repeater-active-item' );
			});
		});
	},

	addItem: function() {
		var repeater = $( this ),
			itemIndex = repeater.data( 'itemIndex' ),
			itemTemplate = repeater.data( 'itemTemplate' );

		repeater.audiothemeRepeater( 'clearList' );

		repeater.find( '.audiotheme-repeater-items' ).append( itemTemplate.clone() )
			.children( ':last-child' ).find( 'input,select,textarea' ).each(function() {
			var $this = $( this );
			$this.attr( 'name', $this.attr( 'name' ).replace( '[0]', '[' + itemIndex + ']' ) );
		}).end()
			.find( '.audiotheme-clear-on-add' ).val( '' ).end()
			.find( '.audiotheme-remove-on-add' ).remove().end()
			.find( '.audiotheme-show-on-add' ).show().end()
			.find( '.audiotheme-hide-on-add' ).hide().end();

		repeater.data( 'itemIndex', itemIndex + 1 ).audiothemeRepeater( 'updateIndex' );

		repeater.trigger( 'addItem.audiotheme', [ repeater.find( '.audiotheme-repeater-items' ).children().last() ]);
	},

	clearList: function() {
		var itemsParent = $( this ).find( '.audiotheme-repeater-items' );

		if ( itemsParent.hasClass( 'is-empty' ) ) {
			itemsParent.removeClass( 'is-empty' ).html( '' );
		}
	},

	updateIndex: function() {
		$( '.audiotheme-repeater-index', this ).each(function( i ) {
			$( this ).text( i + 1 + '.' );
		});
	}
};

$.fn.audiothemeRepeater = function( method ) {
	if ( methods[ method ] ) {
		return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
	} else if ( typeof method === 'object' || ! method ) {
		return methods.init.apply( this, arguments );
	} else {
		$.error( 'Method ' + method + ' does not exist on jQuery.audiothemeRepeater' );
	}
};
