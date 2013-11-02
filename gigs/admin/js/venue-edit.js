jQuery(function($) {
	var $city = $('#venue-city'),
		$state = $('#venue-state'),
		$country = $('#venue-country'),
		$venueTz = $('#venue-timezone-string');

	$city.autocomplete({
		source: function( request, response ) {
			$.ajax({
				url: 'http://ws.geonames.org/searchJSON',
				data: {
					featureClass: 'P',
					style: 'full',
					maxRows: 12,
					name_startsWith: request.term
				},
				dataType: 'jsonp',
				success: function( data ) {
					response( $.map( data.geonames, function( item ) {
						return {
							label: item.name + (item.adminName1 ? ', ' + item.adminName1 : '') + ', ' + item.countryName,
							value: item.name,
							adminCode: item.adminCode1,
							countryName: item.countryName,
							timezone: item.timezone.timeZoneId
						};
					}));
				}
			});
		},
		minLength: 2,
		select: function(e, ui) {
			if ('' === $state.val()) {
				$state.val(ui.item.adminCode);
			}

			if ('' === $country.val()) {
				$country.val(ui.item.countryName);
			}

			$venueTz.find('option[value="' + ui.item.timezone + '"]').attr('selected','selected');
		}
	});

	/*$city.autocomplete({
		source: function( request, response ) {
			$.ajax({
				url: 'http://ws.geonames.org/searchJSON',
				data: {
					featureClass: 'P',
					style: 'full',
					maxRows: 12,
					name_startsWith: request.term
				},
				dataType: 'jsonp',
				success: function( data ) {
					response( $.map( data.geonames, function( item ) {
						return {
							label: item.name + (item.adminName1 ? ", " + item.adminName1 : "") + ", " + item.countryName,
							value: item.name,
							adminCode: item.adminCode1,
							countryName: item.countryName,
							timezone: item.timezone.timeZoneId
						}
					}));
				}
			});
		},
		minLength: 2,
		select: function(e, ui) {
			if ('' == $state.val())
				$state.val(ui.item.adminCode);

			if ('' == $country.val())
				$country.val(ui.item.countryName);

			$venueTz.find('option[value="' + ui.item.timezone + '"]').attr('selected','selected');
		}
	});*/
});