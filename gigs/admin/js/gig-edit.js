jQuery(function($) {
	var $date = $('#gig-date'),
		$time = $('#gig-time'),
		$venue = $('#gig-venue'),
		$venueTz = $('#gig-venue-timezone');
	
	$venueTz.pointer({ audiothemeId: 'at100_gigvenue_tz' });
	
	$date.datepicker({ showOn: 'both', buttonImage: audiothemeGigsL10n.datepickerIcon });
	
	$time.timepicker({
		'timeFormat': audiothemeGigsL10n.timeFormat,
		'className': 'ui-autocomplete'
	})
	.on('showTimepicker', function() { $(this).addClass('open'); $('.ui-timepicker-list').width( $(this).outerWidth() ); })
	.on('hideTimepicker', function() { $(this).removeClass('open'); })
	.next().on('click', function(e) { $time.focus(); });
	
	$venue.autocomplete({
		change: function() {
			if ( '' != $venue.val() ) {
				$.ajax({
					url: ajaxurl,
					data: {
						action: 'ajax_is_new_audiotheme_venue',
						name: $venue.val()
					},
					dataType: 'JSON',
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
		source: ajaxurl + '?action=ajax_get_audiotheme_venue_matches',
		minLength: 0,
		position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
		open: function() { $(this).addClass('open'); },
		close: function() { $(this).removeClass('open'); }
	});
	
	$('#gig-venue-select').on('click', function() {
		$venue.focus().autocomplete('search','');
	});
});