<?php
/**
 * Set up video-related functionality in the AudioTheme framework.
 *
 * @package AudioTheme_Framework
 * @subpackage Videos
 */

/**
 * Attach hooks for loading and managing videos in the admin dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_load_videos_admin() {
	add_action( 'save_post', 'audiotheme_video_save_post', 10, 2 );
	add_action( 'wp_ajax_audiotheme_get_video_oembed_data', 'audiotheme_ajax_get_video_oembed_data' );

	add_action( 'load-edit.php', 'audiotheme_video_list_help' );
	add_action( 'load-post.php', 'audiotheme_video_help' );
	add_action( 'load-post-new.php', 'audiotheme_video_help' );
	add_filter( 'post_updated_messages', 'audiotheme_video_post_updated_messages' );
	add_filter( 'manage_edit-audiotheme_video_columns', 'audiotheme_video_register_columns' );
	add_filter( 'admin_post_thumbnail_html', 'audiotheme_video_admin_post_thumbnail_html', 10, 2 );

	wp_register_script( 'audiotheme-video-edit', AUDIOTHEME_URI . 'modules/videos/admin/js/video-edit.js', array( 'jquery' ) );

	// Videos Archive
	add_action( 'add_audiotheme_archive_settings_meta_box_audiotheme_video', '__return_true' );
	add_action( 'save_audiotheme_archive_settings', 'audiotheme_video_archive_save_settings_hook', 10, 3 );
	add_action( 'audiotheme_archive_settings_meta_box', 'audiotheme_video_archive_settings' );
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
		1  => sprintf( __( 'Video updated. <a href="%s">View Video</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme' ),
		3  => __( 'Custom field deleted.', 'audiotheme' ),
		4  => __( 'Video updated.', 'audiotheme' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Video published. <a href="%s">View Video</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Video saved.', 'audiotheme' ),
		8  => sprintf( __( 'Video submitted. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Video</a>', 'audiotheme' ),
		      /* translators: Publish box date format, see http://php.net/date */
		      date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Video draft updated. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
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
	$image_column = array( 'audiotheme_image' => _x( 'Image', 'column name', 'audiotheme' ) );
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
			<label for="audiotheme-video-url" class="screen-reader-text"><?php _e( 'Video URL:', 'audiotheme' ); ?></label>
			<input type="text" name="_video_url" id="audiotheme-video-url" value="<?php echo esc_url( $video ); ?>" placeholder="<?php esc_attr_e( 'Video URL', 'audiotheme' ); ?>" class="widefat"><br>

			<span class="description">
				<?php
				printf( __( 'Enter a video URL from one of the %s.', 'audiotheme' ),
					'<a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank">' . __( 'supported video services', 'audiotheme' ) . '</a>'
				);
				?>
			</span>
		</p>

		<div id="audiotheme-video-preview" class="audiotheme-video-preview<?php echo ( $video ) ? '' : ' audiotheme-video-preview-empty'; ?>">
			<?php
			if( $video ) {
				echo get_audiotheme_video( $post->ID, array( 'width' => 600 ) );
			} else {
				_e( 'Save the video after entering a URL to preview it.', 'audiotheme' );
			}
			?>
		</div>
	</div>
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
	if ( 'audiotheme_video' === get_post_type( $post_id ) ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$oembed_thumb_id = get_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id', true );

		$content .= sprintf( '<p id="audiotheme-select-oembed-thumb" class="hide-if-no-js" data-thumb-id="%s" data-oembed-thumb-id="%s">', $thumbnail_id, $oembed_thumb_id );
			$content .= sprintf( '<a href="#" id="audiotheme-select-oembed-thumb-button">%s</a>', __( 'Get video thumbnail', 'audiotheme' ) );
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

		if ( ( ! $current_thumb_id || $current_thumb_id !== $oembed_thumb_id ) && $data->thumbnail_url === $oembed_thumb ) {
			// Re-use the existing oEmbed data instead of making another copy of the thumbnail.
			set_post_thumbnail( $post_id, $oembed_thumb_id );
		} elseif ( ! $current_thumb_id || $data->thumbnail_url !== $oembed_thumb ) {
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

/**
 * Save video archive sort order.
 *
 * The $post_id and $post parameters will refer to the archive CPT, while the
 * $post_type parameter references the type of post the archive is for.
 *
 * @since 1.4.4
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 * @param string $post_type The type of post the archive lists.
 */
function audiotheme_video_archive_save_settings_hook( $post_id, $post, $post_type ) {
	if ( 'audiotheme_video' !== $post_type ) {
		return;
	}

	$orderby = ( isset( $_POST['audiotheme_orderby'] ) ) ? $_POST['audiotheme_orderby'] : '';
	update_post_meta( $post_id, 'orderby', $orderby );
}

/**
 * Add an orderby setting to the video archive.
 *
 * Allows for changing the sort order of videos. Custom would require a plugin
 * like Simple Page Ordering.
 *
 * @since 1.4.4
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_video_archive_settings( $post ) {
	$post_type = is_audiotheme_post_type_archive_id( $post->ID );
	if ( 'audiotheme_video' !== $post_type ) {
		return;
	}

	$options = array(
		'post_date'    => __( 'Publish Date', 'audiotheme' ),
		'title'        => __( 'Title', 'audiotheme' ),
		'custom'       => __( 'Custom', 'audiotheme' ),
	);

	$orderby = get_audiotheme_archive_meta( 'orderby', true, 'post_date', 'audiotheme_video' );
	?>
	<p>
		<label for="audiotheme-orderby"><?php _e( 'Order by:', 'audiotheme' ); ?></label>
		<select name="audiotheme_orderby" id="audiotheme-orderby">
			<?php
			foreach ( $options as $id => $value ) {
				printf( '<option value="%s"%s>%s</option>',
					esc_attr( $id ),
					selected( $id, $orderby, false ),
					esc_html( $value )
				);
			}
			?>
		</select>
	</p>
	<?php
}

/**
 * Add a help tab to the video list screen.
 *
 * @since 1.0.0
 */
function audiotheme_video_list_help() {
	if ( 'audiotheme_video' !== get_current_screen()->post_type ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'audiotheme' ),
		'content' =>
			'<p>' . __( "Using the video panel, AudioTheme allows you to collect your videos from a wide variety of supported services and present them to your fans on your website.", 'audiotheme' ) . '</p>' .
			'<p>' . __( 'This screen provides access to all of your videos. You can customize the display of this screen to suit your workflow.', 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'screen-content',
		'title'   => __( 'Screen Content', 'audiotheme' ),
		'content' =>
			'<p>' . __( "You can customize the appearance of this screen's content in a number of ways:", 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "You can hide or display columns based on your needs and decide how many videos to list per screen using the Screen Options tab.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can filter the list of videos by status using the text links in the upper left to show Upcoming, Past, All, Published, Draft, or Trashed videos. The default view is to show all videos.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can refine the list to show only videos from a specific month by using the dropdown menus above the videos list. Click the Filter button after making your selection.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can also sort your videos in any view by clicking the column headers.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'available-actions',
		'title'   => __( 'Available Actions', 'audiotheme' ),
		'content' =>
			'<p>' . __( "Hovering over a row in the videos list will display action links that allow you to manage your video. You can perform the following actions:", 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "<strong>Edit</strong> takes you to the editing screen for that video. You can also reach that screen by clicking on the video title.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Quick Edit</strong> provides inline access to the metadata of your video, allowing you to update video details without leaving this screen.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Trash</strong> removes your video from this list and places it in the trash, from which you can permanently delete it.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Preview</strong> will show you what your draft video will look like if you publish it.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>View</strong> will take you to your live site to view the video. Which link is available depends on your video's status.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );
}

/**
 * Add a help tab to the add/edit video screen.
 *
 * @since 1.0.0
 */
function audiotheme_video_help() {
	if ( 'audiotheme_video' !== get_current_screen()->post_type ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'standard-fields',
		'title'   => __( 'Standard Fields', 'audiotheme' ),
		'content' =>
			'<p>' . __( "<strong>Title</strong> - Enter a title for your video. After you enter a title, you'll see the permalink below, which you can edit.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Video URL</strong> - Enter the URL for your video. After saving a preview of the video will display below this field.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Editor</strong> - Describe your video. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your description text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular editor.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Excerpt</strong> - Depending on the theme you have activated, this is a brief expert that may appear in your list of videos. Visit the WordPress Support section to <a href=\"http://en.support.wordpress.com/splitting-content/excerpts/\" target=\"_blank\">learn more about excerpts</a>.", 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'featured-image',
		'title'		=> __( 'Featured Image', 'audiotheme' ),
		'content' 	=> '<p>' . __( "This is used to set the thumbnail that will represent your video throughout your site. Make it interesting. You can choose the image through the normal process of setting or a featured image, or click the 'Get video thumbnail link' (you'll need to add a URL to the Video URL field for this to appear) to grab the image directly from the video service. Find out more about <a href=\"http://codex.wordpress.org/Post_Thumbnails\" target=\"_blank\">setting featured images</a> in the WordPress Codex.", 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'customize-display',
		'title'   => __( 'Customize This Screen', 'audiotheme' ),
		'content' => '<p>' . __( 'The title, video url, and big editing area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide or unhide boxes or to choose a 1 or 2-column layout for this screen.', 'audiotheme' ) . '</p>',
	) );
}
