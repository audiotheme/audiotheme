(function($) {
AudioThemeToggleVideoThumbLink = function() {
	var $el = $('#audiotheme-select-oembed-thumb'),
		$videoUrl = $('#audiotheme-video-url'),
		thumbId = $el.data('thumb-id'),
		oembedId = $el.data('oembed-thumb-id');

	$el.toggle( '' == $videoUrl.val() || ( thumbId && thumbId == oembedId ) ? false : true );
}
})(jQuery);

jQuery(function($) {
	var audiothemeThumb, toggleThumbLink, $spinner,
		$thumbDiv = $('.inside', '#postimagediv');

	$('#audiotheme-video-url').on('change', AudioThemeToggleVideoThumbLink)
	$('#audiotheme-video-preview').fitVids();

	// Retrieve the oEmbed thumbnail when the button is clicked.
	$thumbDiv.on( 'click', '#audiotheme-select-oembed-thumb-button', function(e) {
		e.preventDefault();

		$spinner = $thumbDiv.find('.spinner').css('display', 'inline-block');

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				'action': 'audiotheme_get_video_oembed_data',
				'post_id': $('#post_ID').val(),
				'video_url': $('#audiotheme-video-url').val()
			},
			dataType: 'json',
			success: function( data ) {
				$spinner.hide();

				// @todo Do some error checking and reporting here.
				WPSetThumbnailID( data.thumbnail_id );
				WPSetThumbnailHTML( data.thumbnail_meta_box_html );
			}
		});
	});
});