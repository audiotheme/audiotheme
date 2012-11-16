/**
 * @see post_thumbnail_meta_box()
 * @todo Add some error messages if something goes wrong.
 * @todo Show the button again if a different thumbnail is chosen.
 * @todo Do the checks regarding oembed thumb id and current thumb id done in audiotheme_video_oembed_dataparse().
 * @todo Only show the button if a URL is present in the video url field.
 */
jQuery(function($) {
	var $audiothemeDiv,
		$spinner,
		$thumbDiv,
		audiothemeDivHtml = $('<div id="audiotheme-select-oembed-thumb"></div>').append( $('<a />', {
			href: '#',
			id: 'audiotheme-select-oembed-thumb-button',
			class: 'button-secondary',
			text: AudiothemeVideoEdit.thumbButtonText
		}) ).append( AudiothemeVideoEdit.spinner );
	
	$('#audiotheme-video-preview').fitVids();
	
	var audiothemeThumb = {
		init : function() {
			if ( $('body').hasClass('branch-3-4') ) {
				$thumbDiv = $('.inside', '#postimagediv');
				audiothemeThumb.initCompat();
			} else {
				$thumbDiv = $('#select-featured-image');
				$thumbDiv.find('a.choose').before( audiothemeDivHtml );
				
				$audiothemeDiv = $('#audiotheme-select-oembed-thumb');
				$spinner = $audiothemeDiv.find('.ajax-loading, .spinner');
			}
			
			// Retrieve the oEmbed thumbnail when the button is clicked.
			$('#postimagediv').on( 'click', '#audiotheme-select-oembed-thumb-button', function(e) {
				e.preventDefault();
				$spinner.show();
				audiothemeThumb.getOembedThumb();
			});
			
			// Show the "Get Video Thumb" button again if the featured image is removed.
			$thumbDiv.on( 'click', '.remove', function() {
				$audiothemeDiv.show();
			});

			// Reintialize since 3.4 trounces all HTML in the meta box.
			$thumbDiv.on( 'click', '#remove-post-thumbnail', function() {
				// Not very elegant, but should work until 3.4 support is dropped.
				setTimeout( function() {
					audiothemeThumb.initCompat();
				}, 1000 );
			});
		},

		initCompat : function() {
			$thumbDiv.append( audiothemeDivHtml );
			$audiothemeDiv = $('#audiotheme-select-oembed-thumb');
			$spinner = $audiothemeDiv.find('.ajax-loading, .spinner');
		},
		
		getOembedThumb : function() {
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
					audiothemeThumb.setImage( data );
				}
			});
		},
		
		setImage : function( data ) {
			
			// Method for setting the thumbnail before WP 3.5.
			if ( $('body').hasClass('branch-3-4') ) {
				WPSetThumbnailID( data.thumbnail_id );
				WPSetThumbnailHTML( data.thumbnail_meta_box_html );
			}

			// Make these checks better? What actually happens when the data returned isn't good.
			else {
				$audiothemeDiv.hide();
				$thumbDiv.toggleClass( 'has-featured-image', -1 != data.thumbnail_id ).find('img').remove();
				$thumbDiv.find('input[name="thumbnail_id"]').val( data.thumbnail_id );
				$('<img />', { src: data.thumbnail_url }).prependTo( $thumbDiv );
			}
		}
	}
	
	audiothemeThumb.init();
});