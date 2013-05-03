(function($) {
	$.fn.audiothemeDeviceClasses = function() {
		var $el = $(this),
			doCallback = true,
			updateClasses;

		updateClasses = function() {
			var w = $el.width();

			if ( w >= 400 ) {
				$el.addClass('min-width-400');
			} else {
				$el.removeClass('min-width-400');
			}

			if ( w >= 600 ) {
				$el.addClass('min-width-600');
			} else {
				$el.removeClass('min-width-600');
			}
		};

		updateClasses();

		$(window).on('resize', function() {
			if ( doCallback ) {
				doCallback = false;

				setTimeout( function() {
					updateClasses();
					doCallback = true;
				}, 500 );
			}
		});

		return this;
	};
})(jQuery);

(function( window, $, undefined ) {
	$('#audiotheme-gig, #audiotheme-gigs').audiothemeDeviceClasses();
})( window, jQuery );