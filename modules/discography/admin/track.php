<?php
/**
 * Track admin functionality.
 *
 * @package AudioTheme_Framework
 * @subpackage Discography
 */

/**
 * Custom sort tracks on the Manage Tracks screen.
 *
 * @since 1.0.0
 *
 * @param object $wp_query The main WP_Query object. Passed by reference.
 */
function audiotheme_tracks_admin_query( $wp_query ) {
	if ( isset( $_GET['post_type'] ) && 'audiotheme_track' === $_GET['post_type'] ) {
		$sortable_keys = array( 'artist' );
		if ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], $sortable_keys ) ) {
			switch ( $_GET['orderby'] ) {
				case 'artist' :
					$meta_key = '_audiotheme_artist';
					break;
			}

			$order = ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) ? 'desc' : 'asc';
			$orderby = ( empty( $orderby ) ) ? 'meta_value' : $orderby;

			$wp_query->set( 'meta_key', $meta_key );
			$wp_query->set( 'orderby', $orderby );
			$wp_query->set( 'order', $order );
		} elseif ( empty( $_GET['orderby'] ) ) {
			// Auto-sort tracks by title.
			$wp_query->set( 'orderby', 'title' );
			$wp_query->set( 'order', 'asc' );
		}

		if ( ! empty( $_GET['post_parent'] ) ) {
			$wp_query->set( 'post_parent', absint( $_GET['post_parent'] ) );
		}
	}
}

/**
 * Register track columns.
 *
 * @since 1.0.0
 *
 * @param array $columns An array of the column names to display.
 * @return array The filtered array of column names.
 */
function audiotheme_track_register_columns( $columns ) {
	$columns['title'] = _x( 'Track', 'column_name', 'audiotheme' );

	$track_columns = array(
		'artist'   => _x( 'Artist', 'column name', 'audiotheme' ),
		'record'   => _x( 'Record', 'column name', 'audiotheme' ),
		'file'     => _x( 'Audio File', 'column name', 'audiotheme' ),
		'download' => _x( 'Downloadable', 'column name', 'audiotheme' ),
		'purchase' => _x( 'Purchase URL', 'column name', 'audiotheme' ),
	);

	$columns = audiotheme_array_insert_after_key( $columns, 'title', $track_columns );

	unset( $columns['date'] );

	return $columns;
}

/**
 * Register sortable track columns.
 *
 * @since 1.0.0
 *
 * @param array $columns Column query vars with their corresponding column id as the key.
 * @return array
 */
function audiotheme_track_register_sortable_columns( $columns ) {
	$columns['artist'] = 'artist';
	$columns['track_count'] = 'tracks';
	$columns['download'] = 'download';

	return $columns;
}

/**
 * Display custom track columns.
 *
 * @since 1.0.0
 *
 * @param string $column_id The id of the column to display.
 * @param int $post_id Post ID.
 */
function audiotheme_track_display_columns( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'artist' :
			echo get_post_meta( $post_id, '_audiotheme_artist', true );
			break;

		case 'download' :
			if ( is_audiotheme_track_downloadable( $post_id ) ) {
				echo '<img src="' . AUDIOTHEME_URI . 'admin/images/download.png" width="16" height="16">';
			}
			break;

		case 'file' :
			$url = get_audiotheme_track_file_url( $post_id );
			if ( $url ) {
				printf( '<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( $url ),
					'<img src="' . AUDIOTHEME_URI . 'admin/images/music-note.png" width="16" height="16">'
				);
			}
			break;

		case 'purchase' :
			$url = get_audiotheme_track_purchase_url( $post_id );
			if ( $url ) {
				printf( '<a href="%1$s" target="_blank"><img src="' . AUDIOTHEME_URI . 'admin/images/link.png" width="16" height="16"></a>',
					esc_url( $url )
				);
			}
			break;

		case 'record' :
			$track = get_post( $post_id );
			$record = get_post( $track->post_parent );

			if ( $record ) {
				printf( '<a href="%1$s">%2$s</a>',
					get_edit_post_link( $record->ID ),
					apply_filters( 'the_title', $record->post_title )
				);
			}
			break;
	}
}

/**
 * Remove quick edit from the track list table.
 *
 * @since 1.0.0
 *
 * @param array $actions List of actions.
 * @param WP_Post $post A post.
 * @return array
 */
function audiotheme_track_list_table_actions( $actions, $post ) {
	if ( 'audiotheme_track' === get_post_type( $post ) ) {
		unset( $actions['inline hide-if-no-js'] );
	}

	return $actions;
}

/**
 * Remove bulk edit from the track list table.
 *
 * @since 1.0.0
 */
function audiotheme_track_list_table_bulk_actions( $actions ) {
	unset( $actions['edit'] );
	return $actions;
}

/**
 * Custom track filter dropdowns.
 *
 * @since 1.0.0
 *
 * @param array $actions List of actions.
 * @return array
 */
function audiotheme_tracks_filters() {
	global $wpdb;

	$screen = get_current_screen();
	$post_parent = empty( $_GET['post_parent'] ) ? 0 : absint( $_GET['post_parent'] );

	if ( 'edit-audiotheme_track' === $screen->id ) {
		$records = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type='audiotheme_record' AND post_status!='auto-draft' ORDER BY post_title ASC" );
		?>
		<select name="post_parent">
			<option value="0"><?php _e( 'View all records', 'audiotheme' ); ?></option>
			<?php
			if ( $records ) {
				foreach ( $records as $record ) {
					echo printf( '<option value="%1$d"%2$s>%3$s</option>',
						esc_attr( $record->ID ),
						selected( $post_parent, $record->ID, false ),
						esc_html( $record->post_title )
					);
				}
			}
			?>
		</select>
		<?php
	}
}

/**
 * Custom rules for saving a track.
 *
 * @since 1.0.0
 * @todo Get ID3 info for remote files.
 *
 * @param int $post_id Post ID.
 */
function audiotheme_track_save_post( $post_id ) {
	$is_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ? true : false;
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['audiotheme_track_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_track_nonce'], 'update-track_' . $post_id ) ) ? true : false;

	// Bail if the data shouldn't be saved or intention can't be verified.
	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	$track = get_post( $post_id );

	$fields = array( 'artist', 'file_url', 'length', 'purchase_url' );
	foreach ( $fields as $field ) {
		$value = ( empty( $_POST[ $field ] ) ) ? '' : $_POST[ $field ];

		if ( 'artist' === $field ) {
			$value = sanitize_text_field( $value );
		} elseif ( 'length' === $field ) {
			$value = preg_replace( '/[^0-9:]/', '', $value );
		} elseif ( ( 'file_url' === $field || 'purchase_url' === $field ) && ! empty( $value ) ) {
			$value = esc_url_raw( $value );
		}

		update_post_meta( $post_id, '_audiotheme_' . $field, $value );
	}

	$is_downloadable = ( empty( $_POST['is_downloadable'] ) ) ? null : 1;
	update_post_meta( $post_id, '_audiotheme_is_downloadable', $is_downloadable );

	audiotheme_record_update_track_count( $track->post_parent );
}

/**
 * Register track meta boxes.
 *
 * @since 1.0.0
 *
 * @param int $post_id Track ID.
 */
function audiotheme_edit_track_meta_boxes( $post ) {
	wp_enqueue_script( 'audiotheme-media' );

	remove_meta_box( 'submitdiv', 'audiotheme_track', 'side' );

	add_meta_box(
		'submitdiv',
		__( 'Publish', 'audiotheme' ),
		'audiotheme_post_submit_meta_box',
		'audiotheme_track',
		'side',
		'high',
		array(
			'force_delete'      => false,
			'show_publish_date' => false,
			'show_statuses'     => array(),
			'show_visibility'   => false,
		)
	);

	add_meta_box(
		'audiotheme-track-details',
		__( 'Track Details', 'audiotheme' ),
		'audiotheme_track_details_meta_box',
		'audiotheme_track',
		'side',
		'high'
	);
}


/**
 * Display track details meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post The track post object being edited.
 */
function audiotheme_track_details_meta_box( $post ) {
	wp_nonce_field( 'update-track_' . $post->ID, 'audiotheme_track_nonce' );
	?>
	<p class="audiotheme-field">
		<label for="track-artist"><?php _e( 'Artist:', 'audiotheme' ) ?></label>
		<input type="text" name="artist" id="track-artist" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_artist', true ) ); ?>" class="widefat">
	</p>

	<p class="audiotheme-field audiotheme-media-control audiotheme-field-upload"
		data-title="<?php esc_attr_e( 'Choose an MP3', 'audiotheme' ); ?>"
		data-update-text="<?php esc_attr_e( 'Use MP3', 'audiotheme' ); ?>"
		data-target="#track-file-url"
		data-return-property="url"
		data-file-type="audio">
		<label for="track-file-url"><?php _e( 'Audio File URL:', 'audiotheme' ) ?></label>
		<input type="url" name="file_url" id="track-file-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_file_url', true ) ); ?>" class="widefat">

		<input type="checkbox" name="is_downloadable" id="track-is-downloadable" value="1"<?php checked( get_post_meta( $post->ID, '_audiotheme_is_downloadable', true ) ); ?>>
		<label for="track-is-downloadable"><?php _e( 'Allow downloads?', 'audiotheme' ) ?></label>

		<a href="#" class="button audiotheme-media-control-choose" style="float: right"><?php _e( 'Upload MP3', 'audiotheme' ); ?></a>
	</p>

	<p class="audiotheme-field">
		<label for="track-length"><?php _e( 'Length:', 'audiotheme' ) ?></label>
		<input type="text" name="length" id="track-length" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_length', true ) ); ?>" placeholder="00:00" class="widefat">
	</p>

	<p class="audiotheme-field">
		<label for="track-purchase-url"><?php _e( 'Purchase URL:', 'audiotheme' ) ?></label>
		<input type="url" name="purchase_url" id="track-purchase-url" value="<?php echo esc_url( get_post_meta( $post->ID, '_audiotheme_purchase_url', true ) ); ?>" class="widefat">
	</p>

	<?php
	if ( ! get_post( $post->post_parent ) ) {
		$records = get_posts( 'post_type=audiotheme_record&orderby=title&order=asc&posts_per_page=-1' );
		if ( $records ) {
			echo '<p class="audiotheme-field">';
				echo '<label for="post-parent">' . __( 'Record:', 'audiotheme' ) . '</label>';
				echo '<select name="post_parent" id="post-parent" class="widefat">';
					echo '<option value=""></option>';

			foreach ( $records as $record ) {
				printf( '<option value="%s">%s</option>',
					$record->ID,
					esc_html( $record->post_title )
				);
			}
				echo '</select>';
				echo '<span class="description">' . __( 'Associate this track with a record.', 'audiotheme' ) . '</span>';
			echo '</p>';
		}
	}
}

/**
 * Add a help tab to the track list screen.
 *
 * @since 1.0.0
 */
function audiotheme_track_list_help() {
	if ( 'audiotheme_track' !== get_current_screen()->post_type ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'audiotheme' ),
		'content' =>
			'<p>' . __( 'Your discography is the window through which listeners are introduced to and discover your music. Encourage that discovery on your website through a detailed and organized history of your recorded output using the AudioTheme discography screen.', 'audiotheme' ) . '</p>' .
			'<p>' . __( 'This screen provides access to all of your tracks. You can customize the display of this screen to suit your workflow.', 'audiotheme' ) . '</p>' .
			'<p><strong><em>' . __( 'Tracks must be add/created through a record and cannot be added without being associated with a record.', 'audiotheme' ) . '</em></strong></p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'screen-content',
		'title'   => __( 'Screen Content', 'audiotheme' ),
		'content' =>
			'<p>' . __( "You can customize the appearance of this screen's content in a number of ways:", 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You can hide or display columns based on your needs and decide how many tracks to list per screen using the Screen Options tab.', 'audiotheme' ) . '</li>' .
			'<li>' . __( 'You can filter the list of tracks by status using the text links in the upper left to show All, Published, Draft, or Trashed records. The default view is to show all tracks.', 'audiotheme' ) . '</li>' .
			'<li>' . __( 'You can refine the list to show only tracks for a specific record by using the dropdown menus above the tracks list. Click the Filter button after making your selection.', 'audiotheme' ) . '</li>' .
			'<li>' . __( 'You can also sort your tracks in any view by clicking the column headers.', 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'available-actions',
		'title'   => __( 'Available Actions', 'audiotheme' ),
		'content' =>
			'<p>' . __( 'Hovering over a row in the tracks list will display action links that allow you to manage your track. You can perform the following actions:', 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that track. You can also reach that screen by clicking on the track name.', 'audiotheme' ) . '</li>' .
			'<li>' . __( '<strong>Trash</strong> removes your track from this list and places it in the trash, from which you can permanently delete it.', 'audiotheme' ) . '</li>' .
			'<li>' . __( '<strong>Preview</strong> will show you what your draft track will look like if you publish it.', 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>View</strong> will take you to your live site to view the track. Which link is available depends on your track's status.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );
}

/**
 * Add a help tab to the add/edit track screen.
 *
 * @since 1.0.0
 */
function audiotheme_track_help() {
	if ( 'audiotheme_track' !== get_current_screen()->post_type ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'standard-fields',
		'title'   => __( 'Standard Fields', 'audiotheme' ),
		'content' =>
			'<p>' . __( '<strong>Title</strong> - Enter the title of your track/song.', 'audiotheme' ) . '</p>' .
			'<p>' . __( '<strong>Editor</strong> - Describe your track. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your description text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular editor.', 'audiotheme' ) . '</p>' .
			'<p>' . __( "When you're done editing a track, click the Update button.", 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'track-details',
		'title'		=> __( 'Track Details', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( 'Provide additional context to your tracks using this box.' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Artist</strong> - Use this field for the main artist of the record.', 'audiotheme' ) . '</li>' .
			// @todo Add link.
			'<li>' . __( '<strong>File URL</strong> - This field lets you attach audio for streaming or download. To attach an audio file, see [How To: Adding Audio to Tracks]', 'audiotheme' ) . '</li>' .
			'<li>' . __( '<strong>Allow Downloads</strong> - Check this field if you would like to allow your fans to download the track from your site.', 'audiotheme' ) . '</li>' .
			'<li>' . __( '<strong>Purchase URL</strong> - Enter a URL where the track can be purchased individually.', 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'inserting-media',
		'title'		=> __( 'Inserting Media', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( 'You can upload and insert media (images, audio, documents, etc.) by clicking the Add Media button. You can select from the images and files already uploaded to the Media Library, or upload new media to add to your gig description. To create an image gallery, select the images to add and click the "Create a new gallery" button.', 'audiotheme' ) . '</p>' .
			'<p>' . __( 'You can also embed media from many popular websites including Twitter, YouTube, Flickr and others by pasting the media URL on its own line into the gig description editor. Please refer to the Codex to <a href="http://codex.wordpress.org/Embeds">learn more about embeds</a>.', 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'customize-display',
		'title'   => __( 'Customize This Screen', 'audiotheme' ),
		'content' => '<p>' . __( 'The title and big editing area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide or unhide boxes or to choose a 1 or 2-column layout for this screen.', 'audiotheme' ) . '</p>',
	) );
}
