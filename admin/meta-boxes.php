<?php
/**
 * Video Metabox Callback
 *
 * @since 1.0
 *
 * @TODO Move css and javascript to external files
 * @TODO The thumbnail should be sufficient for a preview, 
 *       but an error message if the video isn't embeddable 
 *       would be helfpul, else show video preview if URL is set.
 *
 */
function audiotheme_video_meta_cb( $post ) {

	// Store the saved values
	$video = get_post_meta( $post->ID, '_video_url', true );

	// Nonce to verify intention later
	wp_nonce_field( 'save_audiotheme_video_meta', 'audiotheme_video_nonce' );

	?>
	<p>
		<?php _e( 'Enter a video URL from one of the WordPress', 'audiotheme-i18n' ) ?> <a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank"><?php _e( 'supported video services.', 'audiotheme-i18n' ); ?></a>
	</p>
	
	<p>
		<input type="text" name="_video_url" value="<?php echo esc_url( $video ); ?>" id="audiotheme-video-url" class="widefat" placeholder="<?php _e( 'Video URL', 'audiotheme-i18n' ); ?>">
	</p>
	
	<div id="audiotheme-video-preview">
		<?php if( $video ) echo wp_oembed_get( $video, array( 'width' => 258 ) ); ?>
	</div>
	
	<p>
		<input type="button" id="button-get-video-data" class="button" value="Get Thumbnail" style="vertical-align: middle">
		<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" id="get-video-data-indicator" class="ajax-indicator">
	</p>
	
	<style type="text/css">
	#audiotheme-video-preview iframe { background: url(<?php echo admin_url( 'images/wpspin_light.gif' ) ?>) center center no-repeat;}
	.ajax-indicator { display: none; margin: 0 0 0 5px; vertical-align: middle;}
	</style>
	
	<script>
	(function($) {
		$('#button-get-video-data').on('click', function(e) {
			var spinner = $('#get-video-data-indicator').show();
			e.preventDefault();
			
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					'action': 'audiotheme_get_video_data',
					'post_id': $('#post_ID').val(),
					'video_url': $('#audiotheme-video-url').val()
				},
				dataType: 'json',
				success: function(data) {
					spinner.hide();
					
					if('undefined' != typeof data.thumbnail_id && '0' != data.thumbnail_meta_box_html) {
						WPSetThumbnailID(data.thumbnail_id);
						WPSetThumbnailHTML(data.thumbnail_meta_box_html);
					}
				}
			});
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * Get Video Data
 *
 * @since 1.0
 */
function audiotheme_get_video_data() {
	global $post_ID;
	
	$post_ID = absint( $_POST['post_id'] );
	
	$json['post_id'] = $post_ID;
	
	add_filter( 'oembed_dataparse', 'audiotheme_oembed_dataparse', 1, 3 );
	$oembed = wp_oembed_get( $_POST['video_url'] );
	
	if ( $thumbnail_id = get_post_thumbnail_id( $post_ID ) ) {
		$json['thumbnail_id'] = $thumbnail_id;
		$json['thumbnail_meta_box_html'] = _wp_post_thumbnail_html( $thumbnail_id );
	}
	
	die( json_encode( $json ) );
}

/**
 * Parse Video oEmbed Data
 *
 * @since 1.0
 */
function audiotheme_oembed_dataparse( $return, $data, $url ) {
	global $post_ID;
	
	// Support for any oEmbed providers that respond with thumbnail_url
	if( isset( $data->thumbnail_url ) ) {
		
		$current_source = get_post_meta( $post_ID, '_thumbnail_source', true );
		$current_source_id = get_post_meta( $post_ID, '_thumbnail_source_id', true );
		
		if ( ! get_post_thumbnail_id( $post_ID ) && $data->thumbnail_url == $current_source ) {
			
			// Re-use the existing source data instead of making another copy of the thumbnail
			set_post_thumbnail( $post_ID, $current_source_id );
		
		} elseif ( ! get_post_thumbnail_id( $post_ID ) || $data->thumbnail_url != $current_source ) {
			
			// Add new thumbnail if the returned URL doesn't match the 
			// current source URL or if there isn't a current thumbnail
			add_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			media_sideload_image( $data->thumbnail_url, $post_ID );
			remove_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			
			if ( $thumbnail_id = get_post_thumbnail_id( $post_ID ) ) {
				
				// store source so we don't copy the same image on repeated requests
				update_post_meta( $post_ID, '_thumbnail_source', $data->thumbnail_url, true );
				update_post_meta( $post_ID, '_thumbnail_source_id', $thumbnail_id, true );
			
			}
		}
	}
	
	return $return;
}

/**
 * Add Video Thumbnail
 *
 * @since 1.0
 */
function audiotheme_add_video_thumbnail( $attachment_id ) {
	global $post_ID;
	set_post_thumbnail( $post_ID, $attachment_id );
}

/**
 * Save Video Metabox Values
 *
 * @since 1.0
 */
function audiotheme_video_save( $id ) {
	// Let's not auto save the data
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return; 

	// Check our nonce
	if( ! isset( $_POST['audiotheme_video_nonce'] ) || ! wp_verify_nonce( $_POST['audiotheme_video_nonce'], 'save_audiotheme_video_meta' ) )
		return;

	// Make sure the current user can edit the post
	if( !current_user_can( 'edit_post' ) )
		return;

	// Make sure we get a clean url here with esc_url
	if( isset( $_POST['_video_url'] ) )
		update_post_meta( $id, '_video_url', esc_url( $_POST['_video_url'], array( 'http', 'https' ) ) );
}
?>