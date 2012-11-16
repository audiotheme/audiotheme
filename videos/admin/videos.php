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
	add_action( 'delete_attachment', 'audiotheme_video_delete_attachment' );
	
	add_action( 'wp_ajax_audiotheme_get_video_oembed_data', 'audiotheme_ajax_get_video_oembed_data' );
	
	add_filter( 'post_updated_messages', 'audiotheme_video_post_updated_messages' );
	add_filter( 'manage_edit-audiotheme_video_columns', 'audiotheme_video_columns' );
	add_action( 'manage_posts_custom_column', 'audiotheme_video_display_column', 10, 2 );
	add_filter( 'audiotheme_nav_menu_archive_items', 'audiotheme_video_archive_menu_item' );
	
	wp_register_script( 'audiotheme-video-edit', AUDIOTHEME_URI . 'videos/admin/js/video-edit.js', array( 'jquery' ) );
	wp_localize_script( 'audiotheme-video-edit', 'AudiothemeVideoEdit', array(
		'spinner' => audiotheme_admin_spinner( array( 'echo' => false ) ),
		'thumbButtonText' => __( 'Get Video Thumbnail', 'audiotheme-i18n' )
	) );
}

/**
 * Video update messages.
 *
 * @since 1.0.0
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of existing post update messages.
 * @return array An array of new video update messages.
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
 * Any additional filters to add or remove columns should set a lower priority
 * since this filter is replacing the $columns variable instead of modifying
 * it. May need to modify this in the future if it becomes problematic.
 *
 * @since 1.0.0
 *
 * @param array $columns An array of the column names to display.
 * @return array The filtered array of column names.
 */
function audiotheme_video_columns( $columns ) {
	$columns = array(
		'cb'               => '<input type="checkbox">',
		'audiotheme_image' => _x( 'Image', 'column name', 'audiotheme-i18n' ),
		'title'            => _x( 'Video', 'column name', 'audiotheme-i18n' ),
		'author'           => _x( 'Author', 'column name', 'audiotheme-i18n' ),
		'video_type'       => _x( 'Type', 'column name', 'audiotheme-i18n' ),
		'tags'             => _x( 'Tags', 'column name', 'audiotheme-i18n' ),
		'date'             => _x( 'Date', 'column name', 'audiotheme-i18n' )
	);
	
	return $columns;
}

/**
 * Display custom video columns.
 *
 * @since 1.0.0
 *
 * @param string $column_id The id of the column to display.
 * @param int $post_id Post ID.
 */
function audiotheme_video_display_column( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'video_type' :
			$taxonomy = 'audiotheme_video_type';
			$post_type = get_post_type( $post_id );
			$video_types = get_the_terms( $post_id, $taxonomy );
			
			if( ! empty( $video_types ) ) {
				foreach ( $video_types as $video_type ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $video_type->slug ) ),
						esc_html( sanitize_term_field( 'name', $video_type->name, $video_type->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} else {
				echo '—';
			}
			break;
	}
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
	
	$video = get_audiotheme_post_video_url( $post->ID );
	wp_nonce_field( 'save-video-meta_' . $post->ID, 'audiotheme_save_video_meta_nonce', false );	
	?>
	<div class="audiotheme-edit-after-title" style="position: relative">
		<p>
			<label for="audiotheme-video-url" class="screen-reader-text">Video URL:</label>
			<input type="text" name="_video_url" id="audiotheme-video-url" value="<?php echo esc_url( $video ); ?>" placeholder="<?php esc_attr_e( 'Video URL', 'audiotheme-i18n' ); ?>" class="widefat"><br>
			
			<span class="description">
				Enter a video URL from one of the
				<a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">supported video services</a>.
			</span>
		</p>
		
		<div id="audiotheme-video-preview" class="audiotheme-video-preview<?php echo ( $video ) ? '' : ' audiotheme-video-preview-empty'; ?>">
			<?php
			if( $video ) {
				echo get_the_audiotheme_post_video( $post->ID, array( 'width' => 600 ) );
			} else {
				echo 'Save the video after entering a URL to preview it.';
			}
			?>
		</div>
	</div>
	
	<style type="text/css">
	.audiotheme-video-preview-empty { padding: 20px 0; color: #aaa; font-size: 20px; text-align: center; border: 4px dashed #ddd;}
	
	#audiotheme-select-oembed-thumb { float: left; clear: both; margin-bottom: 10px; width: 100%;}
	#audiotheme-select-oembed-thumb .spinner { float: left;}

	.branch-3-4 #audiotheme-select-oembed-thumb { float: none;}
	.branch-3-4 #audiotheme-select-oembed-thumb .spinner { display: none; visibility: visible; float: none; margin-left: 5px; vertical-align: middle;}
	</style>
	<?php
}

/**
 * AJAX method to retrieve the thumbnail for a video.
 *
 * @since 1.0.0
 * @todo Display an error if it doesn't work.
 * @todo Use use wp_send_json_success() or wp_send_json_failure()?
 */
function audiotheme_ajax_get_video_oembed_data() {
	global $post_ID;
	
	$post_ID = absint( $_POST['post_id'] );
	
	$json['post_id'] = $post_ID;
	
	add_filter( 'oembed_dataparse', 'audiotheme_parse_video_oembed_data', 1, 3 );
	$oembed = wp_oembed_get( $_POST['video_url'] );
	
	if ( $thumbnail_id = get_post_thumbnail_id( $post_ID ) ) {
		$json['thumbnail_id'] = $thumbnail_id;
		$json['thumbnail_url'] = wp_get_attachment_url( $thumbnail_id );
		
		if ( audiotheme_version_compare( 'wp', '3.5-beta-1', '<' ) ) {
			$json['thumbnail_meta_box_html'] = _wp_post_thumbnail_html( $thumbnail_id );
		}
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
	global $post_ID;
	
	// Supports any oEmbed providers that respond with 'thumbnail_url'.
	if( isset( $data->thumbnail_url ) ) {	
		$current_thumb_id = get_post_thumbnail_id( $post_ID );
		$oembed_thumb_id = get_post_meta( $post_ID, '_audiotheme_oembed_thumbnail_id', true );
		$oembed_thumb = get_post_meta( $post_ID, '_audiotheme_oembed_thumbnail_url', true );
		
		if ( ( ! $current_thumb_id || $current_thumb_id != $oembed_thumb_id ) && $data->thumbnail_url == $oembed_thumb ) {
			// Re-use the existing oEmbed data instead of making another copy of the thumbnail.
			set_post_thumbnail( $post_ID, $oembed_thumb_id );
		} elseif ( ! $current_thumb_id || $data->thumbnail_url != $oembed_thumb ) {
			// Add new thumbnail if the returned URL doesn't match the 
			// oEmbed thumb URL or if there isn't a current thumbnail.
			add_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			media_sideload_image( $data->thumbnail_url, $post_ID );
			remove_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			
			if ( $thumbnail_id = get_post_thumbnail_id( $post_ID ) ) {
				// Store the oEmbed thumb data so the same image isn't copied on repeated requests.
				update_post_meta( $post_ID, '_audiotheme_oembed_thumbnail_id', $thumbnail_id, true );
				update_post_meta( $post_ID, '_audiotheme_oembed_thumbnail_url', $data->thumbnail_url, true );
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
	global $post_ID;
	set_post_thumbnail( $post_ID, $attachment_id );
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

/**
 * Delete oembed thumbnail post meta if the associated attachment is deleted.
 *
 * @since 1.0.0
 *
 * @param int $attachment_id The ID of the attachment being deleted.
 */
function audiotheme_video_delete_attachment( $attachment_id ) {
	global $wpdb;
	
	$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_audiotheme_oembed_thumbnail_id' AND meta_value=%d", $attachment_id ) );
	if ( $post_id ) {
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id' );
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url' );
	}
}

/**
 * Add a nav menu item to the custom AudioTheme archive meta box for the
 * video archive.
 *
 * @since 1.0.0
 *
 * @param array $items List of existing nav menu items.
 * @return array List of filtered nav menu items.
 */
function audiotheme_video_archive_menu_item( $items ) {
	$items[] = array(
		'title' => _x( 'Videos', 'nav menu archive label', 'audiotheme-i18n' ),
		'post_type' => 'audiotheme_video',
		'url' => get_post_type_archive_link( 'audiotheme_video' )
	);
	
	return $items;
}
?>