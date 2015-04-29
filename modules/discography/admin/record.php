<?php
/**
 * Record admin functionality.
 *
 * @package AudioTheme_Framework
 * @subpackage Discography
 */

/**
 * Custom sort records on the Manage Records screen.
 *
 * @since 1.0.0
 *
 * @param object $wp_query The main WP_Query object. Passed by reference.
 */
function audiotheme_records_admin_query( $wp_query ) {
	if ( is_admin() && isset( $_GET['post_type'] ) && 'audiotheme_record' == $_GET['post_type'] ) {
		$sortable_keys = array( 'artist', 'release_year', 'tracks' );
		if ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], $sortable_keys ) ) {
			switch ( $_GET['orderby'] ) {
				case 'release_year' :
					$meta_key = '_audiotheme_release_year';
					$orderby = 'meta_value_num';
					break;
				case 'tracks' :
					$meta_key = '_audiotheme_track_count';
					$orderby = 'meta_value_num';
					break;
			}

			$order = ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] ) ? 'desc' : 'asc';
			$orderby = ( empty( $orderby ) ) ? 'meta_value' : $orderby;

			$wp_query->set( 'meta_key', $meta_key );
			$wp_query->set( 'orderby', $orderby );
			$wp_query->set( 'order', $order );
		}
	}
}

/**
 * Register record columns.
 *
 * @since 1.0.0
 *
 * @param array $columns An array of the column names to display.
 * @return array Filtered array of column names.
 */
function audiotheme_record_register_columns( $columns ) {
	$columns['title'] = _x( 'Record', 'column_name', 'audiotheme' );

	// Create columns and insert them in the appropriate position in the columns array.
	$image_column = array( 'audiotheme_image' => _x( 'Image', 'column name', 'audiotheme' ) );
	$release_column = array( 'release_year' => _x( 'Released', 'column_name', 'audiotheme' ) );
	$columns = audiotheme_array_insert_after_key( $columns, 'cb', $image_column );
	$columns = audiotheme_array_insert_after_key( $columns, 'title', $release_column );

	$columns['track_count'] = _x( 'Tracks', 'column name', 'audiotheme' );

	unset( $columns['date'] );

	return $columns;
}

/**
 * Register sortable record columns.
 *
 * @since 1.0.0
 *
 * @param array $columns Column query vars with their corresponding column id as the key.
 * @return array
 */
function audiotheme_record_register_sortable_columns( $columns ) {
	$columns['release_year'] = 'release_year';
	$columns['track_count'] = 'tracks';

	return $columns;
}

/**
 * Display custom record columns.
 *
 * @since 1.0.0
 *
 * @param string $column_id The id of the column to display.
 * @param int $post_id Post ID.
 */
function audiotheme_record_display_columns( $column_name, $post_id ) {
	global $post;

	switch ( $column_name ) {
		case 'release_year' :
			echo get_audiotheme_record_release_year( $post_id );
			break;

		case 'track_count' :
			$args = array(
				'post_type' => 'audiotheme_track',
				'post_parent' => $post_id,
			);

			printf( '<a href="%s">%s</a>',
				add_query_arg( $args, esc_url( admin_url( 'edit.php' ) ) ),
				get_post_meta( $post_id, '_audiotheme_track_count', true )
			);
			break;
	}
}

/**
 * Remove quick edit from the record list table.
 *
 * @since 1.0.0
 *
 * @param array $actions List of actions.
 * @param WP_Post $post A post.
 * @return array
 */
function audiotheme_record_list_table_actions( $actions, $post ) {
	if ( 'audiotheme_record' == get_post_type( $post ) ) {
		unset( $actions['inline hide-if-no-js'] );
	}

	return $actions;
}

/**
 * Remove bulk edit from the record list table.
 *
 * @since 1.0.0
 *
 * @param array $actions List of actions.
 * @return array
 */
function audiotheme_record_list_table_bulk_actions( $actions ) {
	unset( $actions['edit'] );

	return $actions;
}

/**
 * Custom rules for saving a record.
 *
 * Creates and updates child tracks and saves additional record meta.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_record_save_post( $post_id ) {
	$is_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ? true : false;
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['audiotheme_record_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_record_nonce'], 'update-record_' . $post_id ) ) ? true : false;

	// Bail if the data shouldn't be saved or intention can't be verified.
	if( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	$current_user = wp_get_current_user();

	// Whitelisted fields.
	$fields = array( 'release_year', 'artist', 'genre' );
	foreach( $fields as $field ) {
		$value = ( empty( $_POST[ $field ] ) ) ? '' : $_POST[ $field ];
		update_post_meta( $post_id, '_audiotheme_' . $field, $value );
	}

	// Update purchase urls.
	$record_links = array();
	if ( isset( $_POST['record_links'] ) && is_array( $_POST['record_links'] ) ) {
		foreach( $_POST['record_links'] as $link ) {
			if ( ! empty( $link['name'] ) && ! empty( $link['url'] ) ) {
				$link['url'] = esc_url_raw( $link['url'] );
				$record_links[] = $link;
			}
		}
	}
	update_post_meta( $post_id, '_audiotheme_record_links', $record_links );

	// Update tracklist.
	if ( ! empty( $_POST['audiotheme_tracks'] ) ) {
		$i = 1;
		foreach ( $_POST['audiotheme_tracks'] as $track_data ) {
			$default_data = array( 'artist' => '', 'post_id' => '', 'title' => '' );
			$track_data = wp_parse_args( $track_data, $default_data );

			$data = array();
			$track_id = ( empty( $track_data['post_id'] ) ) ? '' : absint( $track_data['post_id'] );

			if ( ! empty( $track_data['title'] ) ) {
				$data['post_title'] = $track_data['title'];
				$data['post_status'] = 'publish';
				$data['post_parent'] = $post_id;
				$data['menu_order'] = $i;
				$data['post_type'] = 'audiotheme_track';

				// Insert or update track.
				if ( empty( $track_id ) ) {
					$track_id = wp_insert_post( $data );
				} else {
					$data['ID'] = $track_id;
					$data['post_author'] = $current_user->ID;

					wp_update_post( $data );
				}

				$i++;
			}

			// Update track artist and file url.
			if ( ! empty( $track_id ) && ! is_wp_error( $track_id ) ) {
				update_post_meta( $track_id, '_audiotheme_artist', $track_data['artist'] );
				update_post_meta( $track_id, '_audiotheme_file_url', $track_data['file_url'] );
			}
		}

		// Update track count.
		audiotheme_record_update_track_count( $post_id );
	}
}

/**
 * Update a record's track count.
 *
 * @since 1.0.0
 *
 * @param int $post_id Record ID.
 */
function audiotheme_record_update_track_count( $post_id ) {
	global $wpdb;

	$track_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='audiotheme_track' AND post_parent=%d", $post_id ) );
	$track_count = ( empty( $track_count ) ) ? 0 : absint( $track_count );
	update_post_meta( $post_id, '_audiotheme_track_count', $track_count );
}

/**
 * Register record meta boxes.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post The record post object being edited.
 */
function audiotheme_edit_record_meta_boxes( $post ) {
	remove_meta_box( 'submitdiv', 'audiotheme_record', 'side' );
	add_meta_box( 'submitdiv', __( 'Publish', 'audiotheme' ), 'audiotheme_post_submit_meta_box', 'audiotheme_record', 'side', 'high', array(
		'force_delete'      => false,
		'show_publish_date' => false,
		'show_statuses'     => array(),
		'show_visibility'   => false,
	) );

	add_meta_box( 'audiotheme-record-details', __( 'Record Details', 'audiotheme' ), 'audiotheme_record_details_meta_box', 'audiotheme_record', 'side', 'high' );

	add_action( 'edit_form_after_editor', 'audiotheme_edit_record_tracklist' );

	wp_enqueue_script( 'jquery-ui-autocomplete' );
}

/**
 * Tracklist editor.
 *
 * @since 1.0.0
 */
function audiotheme_edit_record_tracklist() {
	global $post;

	wp_enqueue_script( 'audiotheme-media' );

	$tracks = get_audiotheme_record_tracks( $post->ID );

	if ( $tracks ) {
		foreach ( $tracks as $key => $track ) {
			$tracks[ $key ] = array(
				'key'          => $key,
				'id'           => $track->ID,
				'title'        => esc_attr( $track->post_title ),
				'artist'       => esc_attr( get_post_meta( $track->ID, '_audiotheme_artist', true ) ),
				'fileUrl'      => esc_attr( get_post_meta( $track->ID, '_audiotheme_file_url', true ) ),
				'downloadable' => is_audiotheme_track_downloadable( $track->ID ),
				'purchaseUrl'  => esc_url( get_post_meta( $track->ID, '_audiotheme_purchase_url', true ) ),
			);
		}
	}

	$thickbox_url = add_query_arg( array(
		'post_id'   => $post->ID,
		'type'      => 'audio',
		'TB_iframe' => true,
		'width'     => 640,
		'height'    => 750,
	), admin_url( 'media-upload.php' ) );

	require( AUDIOTHEME_DIR . 'modules/discography/admin/views/edit-record-tracklist.php' );
}

/**
 * Record details meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post The record post object being edited.
 */
function audiotheme_record_details_meta_box( $post ) {
	// Nonce to verify intention later.
	wp_nonce_field( 'update-record_' . $post->ID, 'audiotheme_record_nonce' );

	$record_links = (array) get_audiotheme_record_links( $post->ID );
	$record_links = ( empty( $record_links ) ) ? array( '' ) : $record_links;

	$record_link_sources = get_audiotheme_record_link_sources();
	$record_link_source_names = array_keys( $record_link_sources );
	sort( $record_link_source_names );

	require( AUDIOTHEME_DIR . 'modules/discography/admin/views/edit-record-details.php' );
}

/**
 * Save record archive sort order.
 *
 * The $post_id and $post parameters will refer to the archive CPT, while the
 * $post_type parameter references the type of post the archive is for.
 *
 * @since 1.3.0
 *
 * @param int $post_id Post ID.
 * @param WP_Post $post Post object.
 * @param string $post_type The type of post the archive lists.
 */
function audiotheme_record_archive_save_settings_hook( $post_id, $post, $post_type ) {
	if ( 'audiotheme_record' != $post_type ) {
		return;
	}

	$orderby = ( isset( $_POST['audiotheme_orderby'] ) ) ? $_POST['audiotheme_orderby'] : '';
	update_post_meta( $post_id, 'orderby', $orderby );
}

/**
 * Add an orderby setting to the record archive.
 *
 * Allows for changing the sort order of records. Custom would require a plugin
 * like Simple Page Ordering.
 *
 * @since 1.3.0
 *
 * @param WP_Post $post Post object.
 */
function audiotheme_record_archive_settings( $post ) {
	$post_type = is_audiotheme_post_type_archive_id( $post->ID );
	if ( 'audiotheme_record' != $post_type ) {
		return;
	}

	$options = array(
		'release_year' => __( 'Release Year', 'audiotheme' ),
		'title'        => __( 'Title', 'audiotheme' ),
		'custom'       => __( 'Custom', 'audiotheme' ),
	);

	$orderby = get_audiotheme_archive_meta( 'orderby', true, 'release_year', 'audiotheme_record' );
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
 * Add a help tab to the record list screen.
 *
 * @since 1.0.0
 */
function audiotheme_record_list_help() {
	if ( 'audiotheme_record' != get_current_screen()->post_type ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'audiotheme' ),
		'content' =>
			'<p>' . __( "Your discography is the window through which listeners are introduced to and discover your music. Encourage that discovery on your website through a detailed and organized history of your recorded output using the AudioTheme discography screen.", 'audiotheme' ) . '</p>' .
			'<p>' . __( 'This screen provides access to all of your records. You can customize the display of this screen to suit your workflow.', 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'screen-content',
		'title'   => __( 'Screen Content', 'audiotheme' ),
		'content' =>
			'<p>' . __( "You can customize the appearance of this screen's content in a number of ways:", 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "You can hide or display columns based on your needs and decide how many records to list per screen using the Screen Options tab.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can filter the list of records by status using the text links in the upper left to show All, Published, Draft, or Trashed records. The default view is to show all records.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "You can also sort your records in any view by clicking the column headers.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'available-actions',
		'title'   => __( 'Available Actions', 'audiotheme' ),
		'content' =>
			'<p>' . __( "Hovering over a row in the records list will display action links that allow you to manage your record. You can perform the following actions:", 'audiotheme' ) . '</p>' .
			'<ul>' .
			'<li>' . __( "<strong>Edit</strong> takes you to the editing screen for that record. You can also reach that screen by clicking on the record name.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Trash</strong> removes your record from this list and places it in the trash, from which you can permanently delete it.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Preview</strong> will show you what your draft record will look like if you publish it.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>View</strong> will take you to your live site to view the record. Which link is available depends on your record's status.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );
}

/**
 * Add a help tab to the add/edit record screen.
 *
 * @since 1.0.0
 */
function audiotheme_record_help() {
	if ( 'audiotheme_record' != get_current_screen()->post_type ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'standard-fields',
		'title'   => __( 'Standard Fields', 'audiotheme' ),
		'content' =>
			'<p>' . __( "<strong>Title</strong> - This is the name of your record. If your record is an album, put the album title here. If it's a single, enter the single's title. If you'd rather add all of your singles as a collection, then you'll need to give that collection a name here.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "<strong>Editor</strong> - Describe your record. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab. Visual mode gives you a WYSIWYG editor. Click the last icon in the row to get a second row of controls. The Text mode allows you to enter HTML along with your description text. Line breaks will be converted to paragraphs automatically. You can insert media files by clicking the icons above the editor and following the directions. You can go to the distraction-free writing screen via the Fullscreen icon in Visual mode (second to last in the top row) or the Fullscreen button in Text mode (last in the row). Once there, you can make buttons visible by hovering over the top area. Exit Fullscreen back to the regular editor.", 'audiotheme' ) . '</p>' .
			'<h4>' . __( 'Track List', 'audiotheme' ) . '</h4>' .
			'<p>' . __( "Tracks are the individual songs that make up your record. Use the Add Track button to quickly add as many as you'd like.", 'audiotheme' ) . '</p>' .
			'<ul> ' .
			'<li>' . __( "<strong>Title</strong> - Enter the title of your track/song here. ", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Artist</strong> - Use this field for the track artist(s). Does the track feature a special guest? Put that here, too.", 'audiotheme' ) . '</li>' .
			// @todo Add link.
			'<li>' . __( "<strong>File URL</strong> - This field lets you attach audio for streaming or download.", 'audiotheme' ) . '</li>' .
			'</ul>' .
			'<p>' . __( "When you're done adding a record, press the Publish button to make it available on your site. If you're not ready to publish, or want to finish updating your record later, press the Save Draft to privately save your progress. You can access your drafts at a later time through the <strong>Discography > All Records</strong> menu.", 'audiotheme' ) . '</p>' .
			'<p>' . __( "After saving the record, you can edit each track individually to add more information.", 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'record-details',
		'title'		=> __( 'Record Details', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( "Provide additional context to your records using this box." ) . '</p>' .
			'<ul>' .
			'<li>' . __( "<strong>Release Year</strong> - Help fans differentiate between new and less new recordings.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Artist</strong> - Use this field for the main artist of the record.", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Genre</strong> - What genre would you classify your record as?", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Type</strong> - Does your record consist of an album or single(s)?", 'audiotheme' ) . '</li>' .
			'<li>' . __( "<strong>Links</strong> - This allows you to link to any additional resources. For example, if you want to direct fans to download your album on Amazon, enter 'Amazon' in the text field and put the web address to your record on Amazon in the URL field. Add as many resources as you would like using the Add URL button.", 'audiotheme' ) . '</li>' .
			'</ul>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'featured-image',
		'title'		=> __( 'Featured Image', 'audiotheme' ),
		'content' 	=> '<p>' . __( "Use the featured image section to add cover art to your record. It's likely this will be used in various place in your theme, so it's best to upload high quality images. Find out more about <a href=\"http://codex.wordpress.org/Post_Thumbnails\" target=\"_blank\">setting featured images</a> in the WordPress Codex.", 'audiotheme' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'		=> 'inserting-media',
		'title'		=> __( 'Inserting Media', 'audiotheme' ),
		'content' 	=>
			'<p>' . __( "Use the Add Media button to attach media to your record. This might be a documentary about the recording process or promotional pictures. Don't add your cover art here, that should be added in the featured image area. Find out more about using the <a href=\"http://codex.wordpress.org/Inserting_Images_into_Posts_and_Pages#Step_2_.E2.80.93_Click_the_Add_Media_button\" target=\"_blank\">Add Media button</a> in the WordPress Codex." ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'customize-display',
		'title'   => __( 'Customize This Screen', 'audiotheme' ),
		'content' => '<p>' . __( 'The title, big editing area, and track list are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to hide or unhide boxes or to choose a 1 or 2-column layout for this screen.', 'audiotheme' ) . '</p>',
	) );
}
