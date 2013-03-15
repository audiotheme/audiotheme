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
	$columns['title'] = _x( 'Record', 'column_name', 'audiotheme-i18n' );

	// Create columns and insert them in the appropriate position in the columns array.
	$image_column = array( 'audiotheme_image' => _x( 'Image', 'column name', 'audiotheme-i18n' ) );
	$release_column = array( 'release_year' => _x( 'Released', 'column_name', 'audiotheme-i18n' ) );
	$columns = audiotheme_array_insert_after_key( $columns, 'cb', $image_column );
	$columns = audiotheme_array_insert_after_key( $columns, 'title', $release_column );

	$columns['record_type'] = _x( 'Type', 'column name', 'audiotheme-i18n' );
	$columns['track_count'] = _x( 'Tracks', 'column name', 'audiotheme-i18n' );

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
		case 'record_type' :
			$taxonomy = 'audiotheme_record_type';
			$post_type = get_post_type( $post_id );
			$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );

			if ( ! empty( $terms ) ) {
				$record_types = get_audiotheme_record_type_strings();
				foreach ( $terms as $term ) {
					if ( isset( $record_types[ $term ] ) ) {
						$names[] = $record_types[ $term ];
					}
				}

				if ( ! empty( $names ) ) {
					echo join( ', ', $names );
				}
			}
			break;

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

	// Update record type.
	$record_types = ( empty( $_POST['record_type'] ) ) ? '' : $_POST['record_type'];
	wp_set_object_terms( $post_id, $record_types, 'audiotheme_record_type' );

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
	add_meta_box( 'submitdiv', __( 'Publish', 'audiotheme-i18n' ), 'audiotheme_post_submit_meta_box', 'audiotheme_record', 'side', 'high', array(
		'force_delete'      => false,
		'show_publish_date' => false,
		'show_statuses'     => array(),
		'show_visibility'   => false,
	) );

	add_meta_box( 'audiotheme-record-details', __( 'Record Details', 'audiotheme-i18n' ), 'audiotheme_record_details_meta_box', 'audiotheme_record', 'side', 'high' );

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

	require( AUDIOTHEME_DIR . 'discography/admin/views/edit-record-tracklist.php' );
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

	$record_types = get_audiotheme_record_type_strings();
	$selected_record_type = wp_get_object_terms( $post->ID, 'audiotheme_record_type', array( 'fields' => 'slugs' ) );

	$record_links = (array) get_audiotheme_record_links( $post->ID );
	$record_links = ( empty( $record_links ) ) ? array( '' ) : $record_links;

	$record_link_sources = get_audiotheme_record_link_sources();
	$record_link_source_names = array_keys( $record_link_sources );
	sort( $record_link_source_names );

	require( AUDIOTHEME_DIR . 'discography/admin/views/edit-record-details.php' );
}
