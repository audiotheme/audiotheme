
var $venueTz = $( '#gig-venue-timezone' ),
	$venueTzSearch = $( '#gig-venue-timezone-search' );

// Automcomplete the search for a city.
$venueTzSearch.autocomplete({
	source: function( request, callback ) {
		$.ajax({
			url: 'http://api.wordpress.org/core/name-to-zoneinfo/1.0/',
			type: 'GET',
			data: {
				s: $venueTzSearch.val()
			},
			dataType: 'jsonp',
			jsonpCallback: 'dummyCallback'
		}).done(function( response ) {
			var data = $.map( response, function( item ) {
				return {
					label: item.name + ', ' + item.location + ' - ' + item.timezone,
					value: item.timezone,
					location: item.location,
					timezone: item.timezone
				};
			});

			callback( data );
		}).fail(function() {
			callback();
		});
	},
	minLength: 2,
	select: function( e, ui ) {
		$venueTz.find( 'option[value="' + ui.item.timezone + '"]' ).attr( 'selected','selected' );
	},
	position:  ( 'undefined' !== typeof isRtl && isRtl ) ? { my: 'right top', at: 'right bottom', offset: '0, -1' } : { offset: '0, -1' },
	open: function() { $( this ).addClass( 'open' ); },
	close: function() { $( this ).removeClass( 'open' ); }
});
