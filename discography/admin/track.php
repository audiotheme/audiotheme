<?php
/**
 * Ensure Track Slugs are Unique
 *
 * Tracks should always be associated with a record so their slugs only need
 * to be unique within the context of a record.
 *
 * @since 1.0.0
 * @todo Remove the "unsuffix" code once < 3.5 is supported.
 */
function audiotheme_track_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug = null ) {
	global $wpdb, $wp_rewrite;
	
	if ( 'audiotheme_track' == $post_type ) {
		$slug = $original_slug;
		
		$feeds = $wp_rewrite->feeds;
		if ( ! is_array( $feeds ) )
			$feeds = array();
		
		// Original slug will only be populated in 3.5 or greater
		// This conditional block can be removed when support is dropped for versions lower than 3.5
		if ( empty( $original_slug ) ) {
			// Did we get suffixed?! If so, try to get the original slug
			// This should only work against the default uniqueness algorithm
			$suffix = end( explode( '-', $slug ) );
			if ( is_numeric( $suffix ) && $suffix > 1 ) {
				$old_slug = $slug;
				$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name=%s AND post_type=%s AND ID!=%d LIMIT 1";
				
				do {
					$prev_post_name = substr( $slug, 0, ( strlen( $suffix ) + 1 ) * -1 ); // remove the suffix
					$prev_post_name = ( 1 === --$suffix ) ? $prev_post_name : $prev_post_name . '-' . $suffix; // append the new suffix
					$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $prev_post_name, $post_type, $post_parent, $post_ID ) );
					if ( $post_name_check ) {
						$old_slug = $prev_post_name; // store the match
					}
				} while( $post_name_check && $suffix > 1 );
				
				// Suffixes due to $post_name_check have been removed
				// Now we need to make sure the previous possible match wasn't suffixed due to matching a feed or filter
				if ( in_array( $prev_post_name, $feeds ) || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $prev_post_name, $post_type ) ) {
					$old_slug = $prev_post_name;
				}
				$slug = $old_slug;
			}
		}
		
		// Make sure the track slug is unique within the context of the record only
		$check_sql = "SELECT post_name FROM $wpdb->posts WHERE post_name=%s AND post_type=%s AND post_parent=%d AND ID!=%d LIMIT 1";
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_type, $post_parent, $post_ID ) );
	
		if ( $post_name_check || apply_filters( 'wp_unique_post_slug_is_bad_flat_slug', false, $slug, $post_type ) ) {
			$suffix = 2;
			do {
				$alt_post_name = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
				$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_type, $post_parent, $post_ID ) );
				$suffix++;
			} while ( $post_name_check );
			$slug = $alt_post_name;
		}
	}
	
	return $slug;
}

/**
 * Custom Sorting on All Tracks Screen
 *
 * @since 1.0.0
 */
function audiotheme_tracks_admin_query( $wp_query ) {
	if ( isset( $_GET['post_type'] ) && 'audiotheme_track' == $_GET['post_type'] ) {
		$sortable_keys = array( 'artist' );
		if ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], $sortable_keys ) ) {
			switch ( $_GET['orderby'] ) {
				case 'artist' :
					$meta_key = '_audiotheme_artist';
					break;
			}
			
			$order = ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] ) ? 'desc' : 'asc';
			$orderby = ( empty( $orderby ) ) ? 'meta_value' : $orderby;
			
			$wp_query->set( 'meta_key', $meta_key );
			$wp_query->set( 'orderby', $orderby );
			$wp_query->set( 'order', $order );
		} elseif ( empty( $_GET['orderby'] ) ) {
			// auto-sort tracks by title
			$wp_query->set( 'orderby', 'title' );
			$wp_query->set( 'order', 'asc' );
		}
		
		if ( ! empty( $_GET['post_parent'] ) ) {
			$wp_query->set( 'post_parent', absint( $_GET['post_parent'] ) );
		}
	}
}

/**
 * Register Track Columns
 *
 * @since 1.0.0
 */
function audiotheme_track_columns( $columns ) {
	$columns = array(
		'cb'       => '<input type="checkbox">',
		'title'    => _x( 'Title', 'column_name', 'audiotheme-i18n' ),
		'artist'   => __( 'Artist', 'audiotheme-i18n' ),
		'record'   => __( 'Record', 'audiotheme-i18n' ),
		'file'     => __( 'Audio File', 'audiotheme-i18n' ),
		'download' => __( 'Downloadable', 'audiotheme-i18n' ),
		'purchase' => __( 'Purchase URL', 'audiotheme-i18n' )
	);
	
	return $columns;
}

/**
 * Display Custom Track Columns
 *
 * @since 1.0.0
 */
function audiotheme_track_display_column( $column_name, $post_id ) {
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
			
			printf( '<a href="%1$s">%2$s</a>',
				get_edit_post_link( $record->ID ),
				apply_filters( 'the_title', $record->post_title )
			);
			break;
	}
}

/**
 * Register Sortable Track Columns
 *
 * @since 1.0.0
 */
function audiotheme_track_sortable_columns( $columns ) {
	$columns['artist'] = 'artist';
	$columns['track_count'] = 'tracks';
	$columns['download'] = 'download';
	
	return $columns;
}

/**
 * Remove Quick Edit from Track List Table
 *
 * @since 1.0.0
 */
function audiotheme_track_list_table_actions( $actions, $post ) {
	if ( 'audiotheme_track' == get_post_type( $post ) ) {
		unset( $actions['inline hide-if-no-js'] );
	}
	
	return $actions;
}

/**
 * Remove Bulk Edit from Track List Table
 *
 * @since 1.0.0
 */
function audiotheme_track_list_table_bulk_actions( $actions ) {
	unset( $actions['edit'] );
	return $actions;
}

/**
 * Custom Track Filter Dropdowns
 *
 * @since 1.0.0
 */
function audiotheme_tracks_filters() {
	global $wpdb;
	
	$screen = get_current_screen();
	
	if ( 'edit-audiotheme_track' == $screen->id ) {
		$records = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE post_type='audiotheme_record' AND post_status!='auto-draft' ORDER BY post_title ASC" );
		?>
		<select name="post_parent">
			<option value="0"><?php _e( 'View all records', 'audiotheme-i18n' ); ?></option>
			<?php
			if ( $records ) {
				foreach ( $records as $record ) {
					echo printf( '<option value="%1$d"%2$s>%3$s</option>',
						esc_attr( $record->ID ),
						selected( $_GET['post_parent'], $record->ID, false ),
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
 * Custom Rules for Saving a Track
 *
 * Updates track meta data.
 *
 * @since 1.0.0
 */
function audiotheme_track_save_hook( $post_id ) {
	global $wpdb;
	
	// Let's not auto save the data
	if( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) )
		return; 

	// Check our nonce
	if( ! isset( $_POST['audiotheme_track_nonce'] ) || ! wp_verify_nonce( $_POST['audiotheme_track_nonce'], 'update-track_' . $post_id ) )
		return;
	
	if ( 'audiotheme_track' != get_post_type( $post_id ) )
		return false;
	
	$current_user = wp_get_current_user();
	
	$fields = array( 'artist', 'file_url', 'purchase_url' );
	foreach( $fields as $field ) {
		$value = ( empty( $_POST[ $field ] ) ) ? '' : $_POST[ $field ];
		update_post_meta( $post_id, '_audiotheme_' . $field, $value );
	}
	
	$is_downloadable = ( empty( $_POST['is_downloadable'] ) ) ? null : 1;
	update_post_meta( $post_id, '_audiotheme_is_downloadable', $is_downloadable );
}

/**
 * Register Track Meta Boxes
 *
 * @since 1.0.0
 */
function audiotheme_edit_track_meta_boxes( $post ) {
	remove_meta_box( 'submitdiv', 'audiotheme_track', 'side' );
	
	add_meta_box( 
		'submitdiv', 
		__( 'Publish', 'audiotheme-i18n' ), 
		'audiotheme_post_submit_meta_box', 
		'audiotheme_track', 
		'side', 
		'high', 
		array(
			'force_delete'      => false,
			'show_publish_date' => false,
			'show_statuses'     => array(),
			'show_visibility'   => false
		) 
	);
	
	add_meta_box( 
		'audiotheme-track-details', 
		__( 'Track Details', 'audiotheme-i18n' ), 
		'audiotheme_track_details_meta_box', 
		'audiotheme_track', 
		'side', 
		'high' 
	);
}


/**
 * Track Details Meta Box
 *
 * @since 1.0.0
 * @todo Consider appending the "Upload MP3" button to the field.
 */
function audiotheme_track_details_meta_box( $post ) {
	// Nonce to verify intention later
	wp_nonce_field( 'update-track_' . $post->ID, 'audiotheme_track_nonce' );
	?>
	<p class="audiotheme-meta-field">
		<label for="track-artist"><?php _e( 'Artist:', 'audiotheme-i18n' ) ?></label>
		<input type="text" name="artist" id="track-artist" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_artist', true ) ) ; ?>" class="widefat">
	</p>
	
	<p class="audiotheme-meta-field audiotheme-meta-field-upload">
		<label for="track-file-url"><?php _e( 'Audio File URL:', 'audiotheme-i18n' ) ?></label>
		<input type="url" name="file_url" id="track-file-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_file_url', true ) ) ; ?>" class="widefat">
		
		<input type="checkbox" name="is_downloadable" id="track-is-downlodable" value="1"<?php checked( get_post_meta( $post->ID, '_audiotheme_is_downloadable', true ) ); ?>>
		<label for="track-is-downloadable"><?php _e( 'Allow downloads?', 'audiotheme-i18n' ) ?></label>
		
		<?php
		$tb_args = array( 
			'post_id' => $post->ID, 
			'type' => 'audio', 
			'TB_iframe' => true, 
			'width' => 640, 
			'height' => 750 
		);
		
		$tb_url = add_query_arg( $tb_args, admin_url( 'media-upload.php' ) );
		?>
		<a href="<?php echo esc_url( $tb_url ); ?>"
		   title="<?php _e( 'Choose a MP3', 'audiotheme-i18n' ); ?>"
		   id="audiotheme-upload-mp3-button"
		   class="button thickbox audiotheme-meta-button"
		   data-insert-field="track-file-url"
		   data-insert-button-text="<?php _e( 'Use MP3', 'audiotheme-i18n' ) ?>"
		   style="float: right"><?php _e( 'Upload MP3', 'audiotheme-i18n' ); ?></a>
	</p>
	
	<p class="audiotheme-meta-field">
		<label for="track-purchase-url"><?php _e( 'Purchase URL:', 'audiotheme-i18n' ) ?></label>
		<input type="url" name="purchase_url" id="track-purchase-url" value="<?php echo esc_url( get_post_meta( $post->ID, '_audiotheme_purchase_url', true ) ) ; ?>" class="widefat">
	</p>

	<?php
}
?>