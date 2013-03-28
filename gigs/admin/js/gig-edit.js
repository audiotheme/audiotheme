jQuery(function($) {
	var $date = $('#gig-date'),
		$time = $('#gig-time'),
		$venue = $('#gig-venue'),
		$venueTzGroup = $('#gig-venue-timezone-group'),
		$venueTz = $('#gig-venue-timezone'),
		$venueTzSearch = $('#gig-venue-timezone-search'),
		ss = sessionStorage || {},
		lastGigDate = 'lastGigDate' in ss ? new Date( ss.lastGigDate ) : null,
		lastGigTime = 'lastGigTime' in ss ? new Date( ss.lastGigTime ) : null,
		dummyCallback = function() {};

	$venueTzGroup.pointer({ audiothemeId: 'at100_gigvenue_tz' });

	// Add a day to the last saved gig date.
	if ( lastGigDate ) {
		lastGigDate.setDate( lastGigDate.getDate() + 1 );
	}

	// Intialize the date picker.
	$date.datepicker({
		dateFormat: 'yy/mm/dd',
		defaultDate: lastGigDate,
		showOn: 'both',
		buttonImage: audiothemeGigsL10n.datepickerIcon
	});

	// Initialize the time picker.
	$time.timepicker({
		'scrollDefaultTime': lastGigTime || '',
		'timeFormat': audiothemeGigsL10n.timeFormat,
		'className': 'ui-autocomplete'
	}).on('showTimepicker', function() {
		$(this).addClass('open');
		$('.ui-timepicker-list').width( $(this).outerWidth() );
	}) .on('hideTimepicker', function() {
		$(this).removeClass('open');
	}) .next().on('click', function(e) {
		$time.focus();
	});

	// Add the last saved date and time to session storage
	// when the gig is saved.
	$('#publish').on('click', function() {
		var date = $date.datepicker('getDate'),
			time = $time.timepicker('getTime');

		if ( ss && '' !== date ) {
			ss.lastGigDate = date;
		}

		if ( ss && '' !== time ) {
			ss.lastGigTime = time;
		}
	});

	// Autocomplete venue names.
	// If the venue is new, show the time zone selection ui.
	$venue.autocomplete({
		change: function() {
			if ( '' !== $venue.val() ) {
				$.ajax({
					url: ajaxurl,
					data: {
						action: 'audiotheme_ajax_is_new_venue',
						name: $venue.val()
					},
					dataType: 'json',
					success: function( data ) {
						if ( data.length ) {
							$venueTzGroup.hide().pointer('close');
						} else {
							$venueTzGroup.show().pointer('audiothemeOpen');
						}
					}
				});
			} else {
				$venueTzGroup.hide().pointer('close');
			}
		},
		select: function() { $venueTzGroup.hide().pointer('close'); },
		source: ajaxurl + '?action=audiotheme_ajax_get_venue_matches',
		minLength: 0,
		position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
		open: function() { $(this).addClass('open'); },
		close: function() { $(this).removeClass('open'); }
	});

	$('#gig-venue-select').on('click', function() {
		$venue.focus().autocomplete('search','');
	});

	// Automcomplete the search for a city.
	$venueTzSearch.autocomplete({
		source: function( request, response ) {
			$.ajax({
				url: 'http://api.wordpress.org/core/name-to-zoneinfo/1.0/',
				type: 'GET',
				data: {
					s: $venueTzSearch.val()
				},
				dataType: 'jsonp',
				jsonpCallback: 'dummyCallback',
				success: function( data ) {
					response( $.map( data, function( item ) {
						return {
							label: item.name + ', ' + item.location + ' - ' + item.timezone,
							value: item.timezone,
							location: item.location,
							timezone: item.timezone
						};
					}));
				}
			});
		},
		minLength: 2,
		select: function(e, ui) {
			$venueTz.find('option[value="' + ui.item.timezone + '"]').attr('selected','selected');
		},
		position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
		open: function() { $(this).addClass('open'); },
		close: function() { $(this).removeClass('open'); }
	});
});