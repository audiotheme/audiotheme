jQuery(function($) {
	var $date = $('#gig-date'),
		$time = $('#gig-time'),
		$venue = $('#gig-venue'),
		$venueTz = $('#gig-venue-timezone'),
		ss = sessionStorage || {},
		lastGigDate = 'lastGigDate' in ss ? new Date( ss.lastGigDate ) : null;
		lastGigTime = 'lastGigTime' in ss ? new Date( ss.lastGigTime ) : null;

	$venueTz.pointer({ audiothemeId: 'at100_gigvenue_tz' });
	
	// Add a day to the last saved gig date.
	lastGigDate ? lastGigDate.setDate( lastGigDate.getDate() + 1 ) : null;
	
	$date.datepicker({
		dateFormat: 'yy/mm/dd',
		defaultDate: lastGigDate,
		showOn: 'both',
		buttonImage: audiothemeGigsL10n.datepickerIcon
	});

	$time.timepicker({
		'scrollDefaultTime': lastGigTime,
		'timeFormat': audiothemeGigsL10n.timeFormat,
		'className': 'ui-autocomplete'
	})
		.on('showTimepicker', function() {
			$(this).addClass('open');
			$('.ui-timepicker-list').width( $(this).outerWidth() );
		})
		.on('hideTimepicker', function() {
			$(this).removeClass('open');
		})
		.next().on('click', function(e) {
			$time.focus();
		});
	
	// Add the last saved date and time to session storage.
	$('#publish').on('click', function() {
		var date = $date.datepicker('getDate'),
			time = $time.timepicker('getTime');
		
		if ( ss && '' != date ) {
			ss.lastGigDate = date;
		}
		
		if ( ss && '' != time ) {
			ss.lastGigTime = time;
		}
	});

	$venue.autocomplete({
		change: function() {
			if ( '' != $venue.val() ) {
				$.ajax({
					url: ajaxurl,
					data: {
						action: 'audiotheme_ajax_is_new_venue',
						name: $venue.val()
					},
					dataType: 'json',
					success: function( data ) {
						if ( data.length ) {
							$venueTz.hide().pointer('close');
						} else {
							$venueTz.show().pointer('audiothemeOpen');
						}
					}
				});
			} else {
				$venueTz.hide().pointer('close');
			}
		},
		select: function() { $venueTz.hide().pointer('close'); },
		source: ajaxurl + '?action=audiotheme_ajax_get_venue_matches',
		minLength: 0,
		position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
		open: function() { $(this).addClass('open'); },
		close: function() { $(this).removeClass('open'); }
	});

	$('#gig-venue-select').on('click', function() {
		$venue.focus().autocomplete('search','');
	});
});