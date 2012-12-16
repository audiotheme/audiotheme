/**
 * Utility to show WP pointers added via audiotheme_enqueue_pointer().
 *
 * @todo This needs to be moved to a file that gets enqueued separately.
 * @todo Consider how to create tours.
 * @todo Build a method to use pointers as tooltips.
 */
(function($) {
	$.widget( 'audiotheme.pointer', $.wp.pointer, {
		options: {},
		
		audiothemeOpen: function( event ) {
			var audiothemePointer;
			
			if( this.options.audiothemeId && 'undefined' !== typeof audiothemePointers ) {
				audiothemePointer = audiothemePointers[ this.options.audiothemeId ] || null;
				
				if ( audiothemePointer ) {
					$.extend( this.options, audiothemePointer );
					
					this._setAudiothemeButtons();
					
					this.open();
				}
			}
		},
		
		audiothemeDismiss: function( event ) {
			/*
			@todo Uncomment this to allow the pointer dismissal to be saved in user meta.
			jQuery.post( ajaxurl, {
				pointer: this.options.audiothemeId,
				action: 'dismiss-wp-pointer'
			});
			*/
			
			this.options.disabled = true; // Prevents the pointer from being reopened.
		},
		
		_setAudiothemeButtons: function( event ) {
			this.options.buttons = function( event, t ) {
				var close  = ( wpPointerL10n ) ? wpPointerL10n.dismiss : 'Dismiss',
					button = $('<a class="close" href="#">' + close + '</a>');
				
				return button.bind( 'click.pointer', function(e) {
					e.preventDefault();
					t.element.pointer('close').pointer('audiothemeDismiss');
				});
			}
		}
	});
})(jQuery);