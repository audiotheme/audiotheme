(function( $, window, undefined ) {
	$.fn.audiothemeDeviceClasses = function() {
		var $el = $(this),
			doCallback = true,
			updateClasses;

		updateClasses = function() {
			var w = $el.outerWidth();

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
})( jQuery, window );

jQuery(function( $ ) {
	// Add media query classes.
	$('.audiotheme-gigs, .audiotheme-gig-single, .audiotheme-records, .audiotheme-record-single, .audiotheme-videos').audiothemeDeviceClasses();

	// Make videos responsive.
	$('.audiotheme-embed').fitVids();

	// Set up track lists.
	$('.audiotheme-record-single').each(function() {
		var $tracklist = $('.audiotheme-tracklist'),
			$tracks = $tracklist.find('.audiotheme-track'),
			$playableTracks;

		if ( 'undefined' === typeof AudiothemeTracks || null === AudiothemeTracks || ! ( 'record' in AudiothemeTracks ) ) {
			return;
		}

		// Loop through enqueued tracks and set up any that have an mp3.
		$.each( AudiothemeTracks.record, function( index, item ) {
			var $player, $track;

			if ( '' === item.mp3 ) {
				return;
			}

			$.jPlayer.timeFormat.padMin = false;
			$track = $( $tracks[ index ] ).addClass('is-playable');
			$track.find('.audiotheme-track-meta').append('<span class="sep-jp-duration"> / </span><span class="jp-duration">--:--</span>').find('.jp-duration, .sep-jp-duration').hide();
			$player = $track.append('<span class="jplayer"></span>').find('.jplayer');

			$player.jPlayer({
				ready: function () {
					$player.jPlayer('setMedia', { mp3: AudiothemeTracks.record[ index ].mp3 });
				},
				swfPath: AudiothemeJplayer.swfPath,
				solution: 'html, flash',
				supplied: 'mp3',
				wmode: 'window',
				cssSelectorAncestor: '#' + $track.attr('id'),
				play: function() {
					$track.addClass('is-playing').find('.jp-duration, .sep-jp-duration').show();
					$player.jPlayer('pauseOthers');
				},
				pause: function() {
					$track.removeClass('is-playing');
				},
				ended: function() {
					var playableIndex = $playableTracks.index( $track );

					$track.removeClass('is-playing');

					// Play the next track.
					if ( playableIndex < $playableTracks.length - 1 ) {
						$playableTracks.eq( playableIndex + 1 ).find('.jplayer').jPlayer('play');
					}
				}
			});

			$playableTracks = $tracks.filter('.is-playable');
		});

		$tracklist.on('click', '.is-playable', function() {
			var $track = $(this),
				$player = $track.find('.jplayer');

			// Determine if the track should start playing.
			if ( $track.hasClass('is-playing') ) {
				$player.jPlayer('pause');
			} else {
				$player.jPlayer('play');
			}
		}).on('click', '.is-playable a', function(e) {
			// Clicks on links shouldn't affect player behavior.
			e.stopPropagation();
		});
	});
});