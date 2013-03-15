<?php
/**
 * Set up video-related functionality in the AudioTheme framework.
 *
 * @package AudioTheme_Framework
 * @subpackage Videos
 */

/**
 * Load videos admin on init.
 */
add_action( 'init', 'audiotheme_load_videos_admin' );

/**
 * Attach hooks for loading and managing videos in the admin dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_load_videos_admin() {
	add_action( 'save_post', 'audiotheme_video_save_post', 10, 2 );
	add_action( 'wp_ajax_audiotheme_get_video_oembed_data', 'audiotheme_ajax_get_video_oembed_data' );

	add_filter( 'post_updated_messages', 'audiotheme_video_post_updated_messages' );
	add_filter( 'manage_edit-audiotheme_video_columns', 'audiotheme_video_register_columns' );
	add_filter( 'admin_post_thumbnail_html', 'audiotheme_video_admin_post_thumbnail_html', 10, 2 );

	wp_register_script( 'audiotheme-video-edit', AUDIOTHEME_URI . 'videos/admin/js/video-edit.js', array( 'jquery' ) );
}

/**
 * Video update messages.
 *
 * @since 1.0.0
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of existing post update messages.
 * @return array
 */
function audiotheme_video_post_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['audiotheme_video'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Video updated. <a href="%s">View Video</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3  => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4  => __( 'Video updated.', 'audiotheme-i18n' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Video published. <a href="%s">View Video</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Video saved.', 'audiotheme-i18n' ),
		8  => sprintf( __( 'Video submitted. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Video</a>', 'audiotheme-i18n' ),
		      // translators: Publish box date format, see http://php.net/date
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Video draft updated. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);

	return $messages;
}

/**
 * Register video columns.
 *
 * @since 1.0.0
 *
 * @param array $columns An array of the column names to display.
 * @return array The filtered array of column names.
 */
function audiotheme_video_register_columns( $columns ) {
	// Register an image column and insert it after the checkbox column.
	$image_column = array( 'audiotheme_image' => _x( 'Image', 'column name', 'audiotheme-i18n' ) );
	$columns = audiotheme_array_insert_after_key( $columns, 'cb', $image_column );

	return $columns;
}

/**
 * Register video meta boxes.
 *
 * This callback is defined in the video CPT registration function. Meta boxes
 * or any other functionality that should be limited to the Add/Edit Video
 * screen and should occur after 'do_meta_boxes' can be registered here.
 *
 * @since 1.0.0
 */
function audiotheme_video_meta_boxes() {
	add_action( 'edit_form_after_title', 'audiotheme_video_after_title' );

	wp_enqueue_script( 'jquery-fitvids' );
	wp_enqueue_script( 'audiotheme-video-edit' );
}

/**
 * Display a field to enter a video URL after the post title.
 *
 * @since 1.0.0
 */
function audiotheme_video_after_title() {
	global $post;

	$video = get_audiotheme_video_url( $post->ID );
	wp_nonce_field( 'save-video-meta_' . $post->ID, 'audiotheme_save_video_meta_nonce', false );
	?>
	<div class="audiotheme-edit-after-title" style="position: relative">
		<p>
			<label for="audiotheme-video-url" class="screen-reader-text"><?php _e( 'Video URL:', 'audiotheme-i18n' ); ?></label>
			<input type="text" name="_video_url" id="audiotheme-video-url" value="<?php echo esc_url( $video ); ?>" placeholder="<?php esc_attr_e( 'Video URL', 'audiotheme-i18n' ); ?>" class="widefat"><br>

			<span class="description">
				<?php
				printf( __( 'Enter a video URL from one of the %s.', 'audiotheme-i18n' ),
					'<a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">' . __( 'supported video services', 'audiotheme-i18n' ) . '</a>'
				);
				?>
			</span>
		</p>

		<div id="audiotheme-video-preview" class="audiotheme-video-preview<?php echo ( $video ) ? '' : ' audiotheme-video-preview-empty'; ?>">
			<?php
			if( $video ) {
				echo get_audiotheme_video( $post->ID, array( 'width' => 600 ) );
			} else {
				_e( 'Save the video after entering a URL to preview it.', 'audiotheme-i18n' );
			}
			?>
		</div>
	</div>
	<style type="text/css">
	.audiotheme-video-preview-empty { padding: 20px 0; color: #aaa; font-size: 20px; text-align: center; border: 4px dashed #ddd;}
	#audiotheme-select-oembed-thumb { display: none; float: none; clear: both; margin-bottom: 10px; width: 100%;}
	#audiotheme-select-oembed-thumb .spinner { display: none; float: none; margin-top: 0; margin-left: 5px; vertical-align: middle;}
	</style>
	<?php
}

/**
 * Add a link to get the video thumbnail from an oEmbed endpoint.
 *
 * Adds data about the current thumbnail and a previously fetched thumbnail
 * from an oEmbed endpoint so the link can be hidden or shown as necessary. A
 * function is also fired each time the HTML is output in order to determine
 * whether the link should be displayed.
 *
 * @since 1.0.0
 *
 * @param string $content Default post thumbnail HTML.
 * @param int $post_id Post ID.
 * @return string
 */
function audiotheme_video_admin_post_thumbnail_html( $content, $post_id ) {
	if ( 'audiotheme_video' == get_post_type( $post_id ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$oembed_thumb_id = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true );

		$content .= sprintf( '<p id="audiotheme-select-oembed-thumb" class="hide-if-no-js" data-thumb-id="%s" data-oembed-thumb-id="%s">', $thumbnail_id, $oembed_thumb_id );
			$content .= sprintf( '<a href="#" id="audiotheme-select-oembed-thumb-button">%s</a>', __( 'Get video thumbnail', 'audiotheme-i18n' ) );
			$content .= audiotheme_admin_spinner( array( 'echo' => false ) );
		$content .= '</p>';

		$content .= '<script>AudioThemeToggleVideoThumbLink();</script>';
	}

	return $content;
}

/**
 * AJAX method to retrieve the thumbnail for a video.
 *
 * @since 1.0.0
 */
function audiotheme_ajax_get_video_oembed_data() {
	global $post_id;

	$post_id = absint( $_POST['post_id'] );
	$json['post_id'] = $post_id;

	add_filter( 'oembed_dataparse', 'audiotheme_parse_video_oembed_data', 1, 3 );
	$oembed = wp_oembed_get( $_POST['video_url'] );

	if ( $thumbnail_id = get_post_thumbnail_id( $post_id ) ) {
		$json['thumbnail_id'] = $thumbnail_id;
		$json['thumbnail_url'] = wp_get_attachment_url( $thumbnail_id );
		$json['thumbnail_meta_box_html'] = _wp_post_thumbnail_html( $thumbnail_id, $post_id );
	}

	wp_send_json( $json );
}

/**
 * Parse video oEmbed data.
 *
 * @since 1.0.0
 * @see WP_oEmbed->data2html()
 *
 * @param string $return Embed HTML.
 * @param object $data Data returned from the oEmbed request.
 * @param string $url The URL used for the oEmbed request.
 * @return string
 */
function audiotheme_parse_video_oembed_data( $return, $data, $url ) {
	global $post_id;

	// Supports any oEmbed providers that respond with 'thumbnail_url'.
	if( isset( $data->thumbnail_url ) ) {
		$current_thumb_id = get_post_thumbnail_id( $post_id );
		$oembed_thumb_id = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true );
		$oembed_thumb = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', true );

		if ( ( ! $current_thumb_id || $current_thumb_id != $oembed_thumb_id ) && $data->thumbnail_url == $oembed_thumb ) {
			// Re-use the existing oEmbed data instead of making another copy of the thumbnail.
			set_post_thumbnail( $post_id, $oembed_thumb_id );
		} elseif ( ! $current_thumb_id || $data->thumbnail_url != $oembed_thumb ) {
			// Add new thumbnail if the returned URL doesn't match the
			// oEmbed thumb URL or if there isn't a current thumbnail.
			add_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			media_sideload_image( $data->thumbnail_url, $post_id );
			remove_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );

			if ( $thumbnail_id = get_post_thumbnail_id( $post_id ) ) {
				// Store the oEmbed thumb data so the same image isn't copied on repeated requests.
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', $thumbnail_id, true );
				update_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url', $data->thumbnail_url, true );
			}
		}
	}

	return $return;
}

/**
 * Set a video post's featured image.
 *
 * @since 1.0.0
 */
function audiotheme_add_video_thumbnail( $attachment_id ) {
	global $post_id;
	set_post_thumbnail( $post_id, $attachment_id );
}

/**
 * Save custom video data.
 *
 * @since 1.0.0
 *
 * @param int $post_id The ID of the post.
 * @param object $post The post object.
 */
function audiotheme_video_save_post( $post_id, $post ) {
	$is_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ? true : false;
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['audiotheme_save_video_meta_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_video_meta_nonce'], 'save-video-meta_' . $post_id ) ) ? true : false;

	// Bail if the data shouldn't be saved or intention can't be verified.
	if( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	if( isset( $_POST['_video_url'] ) ) {
		update_post_meta( $post_id, '_audiotheme_video_url', esc_url_raw( $_POST['_video_url'] ) );
	}
}
