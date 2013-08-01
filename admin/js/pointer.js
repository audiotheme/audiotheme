/*global ajaxurl:true, audiothemePointers:true, wpPointerL10n:true */

/**
 * Utility to show WP pointers added via audiotheme_enqueue_pointer().
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
			// Save the pointer dismissal to user meta.
			jQuery.post( ajaxurl, {
				pointer: this.options.audiothemeId,
				action: 'dismiss-wp-pointer'
			});

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
			};
		}
	});
})(jQuery);