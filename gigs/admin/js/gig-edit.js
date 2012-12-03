jQuery(function($) {
	var $date = $('#gig-date'),
		$time = $('#gig-time'),
		$venue = $('#gig-venue'),
		$venueTz = $('#gig-venue-timezone');
	
	$date.datepicker({ showOn: 'both', buttonImage: audiothemeGigsL10n.datepickerIcon });
	
	//$('#gig-time').timepicker({ show24Hours: false, step: 15 });
	$time.timepicker({ 'timeFormat': audiothemeGigsL10n.timeFormat })
		.on('focus', function() {
			$('.ui-timepicker-list').width( $(this).outerWidth() );
		})
		.next().on('click', function(e) {;
			$time.focus();
		});
	
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
							$venueTz.hide();
						} else {
							$venueTz.show().audiothemePointer( 'at100_gigvenue_tz' );
						}
					}
				});
			} else {
				$venueTz.hide();
			}
		},
		select: function() {
			$venueTz.hide();
		},
		source: function( request, response ) {
			$.ajax({
				url: ajaxurl,
				data: {
					action: 'ajax_get_audiotheme_venue_matches',
					name: request.term
				},
				dataType: 'JSON',
				success: function( data ) { response( data ); }
			});
		},
		minLength: 0
	});
	
	$('#gig-venue-select').on('click', function() {
		$venue.focus().autocomplete('search','');
	});
});