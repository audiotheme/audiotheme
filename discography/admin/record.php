<?php
/**
 * Custom Sorting on All Records Screen
 *
 * @since 1.0.0
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
 * Register Record List Columns
 *
 * @since 1.0.0
 */
function audiotheme_record_columns( $columns ) {
	$columns = array(
		'cb'               => '<input type="checkbox">',
		'audiotheme_image' => __( 'Image', 'audiotheme-i18n' ),
		'title'            => _x( 'Record', 'column_name', 'audiotheme-i18n' ),
		'release_year'     => __( 'Released', 'audiotheme-i18n' ),
		'record_type'      => __( 'Type', 'audiotheme-i18n' ),
		'tags'             => __( 'Tags', 'audiotheme-i18n' ),
		'track_count'      => __( 'Tracks', 'audiotheme-i18n' )
	);
	
	return $columns;
}

/**
 * Display Custom Record Columns
 *
 * @since 1.0.0
 */
function audiotheme_record_display_column( $column_name, $post_id ) {
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
				'post_parent' => $post_id
			);

			printf( '<a href="%s">%s</a>',
				add_query_arg( $args, esc_url( admin_url( 'edit.php' ) ) ),
				get_post_meta( $post_id, '_audiotheme_track_count', true )
			);
			break;
	}
}

/**
 * Register Sortable Record Columns
 *
 * @since 1.0.0
 */
function audiotheme_record_sortable_columns( $columns ) {
	$columns['release_year'] = 'release_year';
	$columns['track_count'] = 'tracks';
	
	return $columns;
}

/**
 * Remove Quick Edit from Record List Table
 *
 * @since 1.0.0
 */
function audiotheme_record_list_table_actions( $actions, $post ) {
	if ( 'audiotheme_record' == get_post_type( $post ) ) {
		unset( $actions['inline hide-if-no-js'] );
	}
	
	return $actions;
}

/**
 * Remove Bulk Edit from Record List Table
 *
 * @since 1.0.0
 */
function audiotheme_record_list_table_bulk_actions( $actions ) {
	unset( $actions['edit'] );

	return $actions;
}

/**
 * Custom Rules for Saving a Record
 *
 * Creates and updates child tracks and saves additional record meta. 
 *
 * @since 1.0.0
 */
function audiotheme_record_save_hook( $post_id ) {
	global $wpdb;
	
	// Let's not auto save the data
	if( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) )
		return; 

	// Check our nonce
	if( ! isset( $_POST['audiotheme_record_nonce'] ) || ! wp_verify_nonce( $_POST['audiotheme_record_nonce'], 'update-record_' . $post_id ) )
		return;
	
	if ( 'audiotheme_record' != get_post_type( $post_id ) )
		return false;
	
	$current_user = wp_get_current_user();
	
	// Whitelisted fields
	$fields = array( 'release_year', 'artist', 'genre' );
	foreach( $fields as $field ) {
		$value = ( empty( $_POST[ $field ] ) ) ? '' : $_POST[ $field ];
		update_post_meta( $post_id, '_audiotheme_' . $field, $value );
	}
	
	// Update purchase urls
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
	
	// Update record type
	$record_types = ( empty( $_POST['record_type'] ) ) ? '' : $_POST['record_type'];
	wp_set_object_terms( $post_id, $record_types, 'audiotheme_record_type' );
	
	// Update tracklist
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
				
				// Insert or update track
				if ( empty( $track_id ) ) {
					$track_id = wp_insert_post( $data );
				} else {
					$data['ID'] = $track_id;
					$data['post_author'] = $current_user->ID;
					
					wp_update_post( $data );
				}
				
				$i++;
			}
			
			// Update track artist and file url
			if ( ! empty( $track_id ) && ! is_wp_error( $track_id ) ) {
				update_post_meta( $track_id, '_audiotheme_artist', $track_data['artist'] );
				update_post_meta( $track_id, '_audiotheme_file_url', $track_data['file_url'] );
			}
		}
		
		// Update track count
		$track_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='audiotheme_track' AND post_parent=%d", $post_id ) );
		$track_count = ( empty( $track_count ) ) ? 0 : absint( $track_count );
		update_post_meta( $post_id, '_audiotheme_track_count', $track_count );
	}
}

/**
 * Register Record Meta Boxes
 *
 * @since 1.0.0
 */
function audiotheme_edit_record_meta_boxes( $post ) {
	remove_meta_box( 'submitdiv', 'audiotheme_record', 'side' );
	add_meta_box( 'submitdiv', __( 'Publish', 'audiotheme-i18n' ), 'audiotheme_post_submit_meta_box', 'audiotheme_record', 'side', 'high', array(
		'force_delete' => false,
		'show_publish_date' => false,
		'show_statuses' => array(),
		'show_visibility' => false
	) );
	
	add_meta_box( 'audiotheme-record-details', __( 'Record Details', 'audiotheme-i18n' ), 'audiotheme_record_details_meta_box', 'audiotheme_record', 'side', 'high' );
	
	add_action( 'edit_form_advanced', 'audiotheme_edit_record_tracklist' );
	
	wp_enqueue_script( 'jquery-ui-autocomplete' );
}

/**
 * Tracklist Editor
 *
 * @since 1.0.0
 */
function audiotheme_edit_record_tracklist() {
	global $post, $wpdb;
	
	$tracks = get_audiotheme_record_tracks( $post->ID );
	
	if ( empty( $tracks ) ) {
		$track = new stdClass();
		$track->ID = '';
		$track->post_title = '';
		
		$tracks[] = $track;
	}
	
	require( AUDIOTHEME_DIR . 'discography/admin/views/edit-record-tracklist.php' );
}

/**
 * Record Details Meta Box
 *
 * @since 1.0.0
 */
function audiotheme_record_details_meta_box( $post ) {
	// Nonce to verify intention later
	wp_nonce_field( 'update-record_' . $post->ID, 'audiotheme_record_nonce' );
	?>
	<p class="audiotheme-meta-field">
		<label for="record-year"><?php _e( 'Release Year', 'audiotheme-i18n' ); ?></label>
		<input type="text" name="release_year" id="record-year" value="<?php echo esc_attr( get_audiotheme_record_release_year( $post->ID ) ) ; ?>" class="widefat">
	</p>
	<p class="audiotheme-meta-field">
		<label for="record-artist"><?php _e( 'Artist', 'audiotheme-i18n' ); ?></label>
		<input type="text" name="artist" id="record-artist" value="<?php echo esc_attr( get_audiotheme_record_artist( $post->ID ) ) ; ?>" class="widefat">
	</p>
	<p class="audiotheme-meta-field">
		<label for="record-genre"><?php _e( 'Genre', 'audiotheme-i18n' ); ?></label>
		<input type="text" name="genre" id="record-genre" value="<?php echo esc_attr( get_audiotheme_record_genre( $post->ID ) ) ; ?>" class="widefat">
	</p>
	<?php
	$record_types = get_audiotheme_record_type_strings();
	$selected_types = wp_get_object_terms( $post->ID, 'audiotheme_record_type', array( 'fields' => 'slugs' ) );
	if ( $record_types ) { ?>
		<p id="audiotheme-record-types" class="audiotheme-meta-field">
			<label><?php _e( 'Type', 'audiotheme-i18n' ) ?></label><br />
				<?php
				foreach ( $record_types as $slug => $name ) {
					echo sprintf( '<input type="radio" name="record_type[]" id="%1$s" value="%1$s"%2$s> <label for="%1$s">%3$s</label><br />',
						esc_attr( $slug ),
						checked( in_array( $slug, $selected_types ), true, false ),
						esc_attr( $name ) );
				}
				?>
		</p>
		<?php
	}
	?>

	<table class="meta-repeater" id="record-links">
		<thead>
			<tr>
				<th colspan="2"><?php _e( 'Links', 'audiotheme_i18n' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">
					<a class="button meta-repeater-add-item"><?php _e( 'Add URL', 'audiotheme-i18n' ) ?></a>
					<?php
					printf( '<span class="meta-repeater-sort-warning" style="display: none;">%1$s <br /><em>%2$s</em></span>',
						esc_html__( 'The order has been changed.', 'audiotheme-i18n' ),
						esc_html__( 'Save your changes.', 'audiotheme-i18n' )
					);
					?>
				</td>
			</tr>
		</tfoot>
		<tbody class="meta-repeater-items">
			<?php
			$record_links = (array) get_audiotheme_record_links( $post->ID );
			$record_links = ( empty( $record_links ) ) ? array( '' ) : $record_links;
			
			foreach( $record_links as $i => $link ) :
				$link = wp_parse_args( $link, array( 'name' => '', 'url' => '' ) );
				?>
				<tr class="meta-repeater-item">
					<td><input type="text" name="record_links[<?php echo $i; ?>][name]" value="<?php echo esc_attr( $link['name'] ); ?>" placeholder="<?php _e( 'Text', 'audiotheme-i18n' ) ?>" class="record-link-name clear-on-add" style="width: 8em"></td>
					<td><input type="text" name="record_links[<?php echo $i; ?>][url]" value="<?php echo esc_url( $link['url'] ); ?>" placeholder="URL" class="widefat clear-on-add"></td>
					<td class="column-action"><a class="meta-repeater-remove-item"><img src="<?php echo esc_url( AUDIOTHEME_URI . '/admin/images/delete.png' ); ?>" width="16" height="16" alt="<?php _e( 'Delete Item', 'audiotheme-i18n' ) ?>" title="<?php _e( 'Delete Item', 'audiotheme-i18n' ) ?>" class="icon-delete" /></a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<?php
	$record_link_sources = get_audiotheme_record_link_sources();
	$record_link_source_names = array_keys( $record_link_sources );
	sort( $record_link_source_names );
	?>
	<script type="text/javascript">
	jQuery(function($) {
		$('#record-links').metaRepeater().on('focus', 'input.record-link-name', function() {
			var $this = $(this);
			
			if ( ! $this.hasClass('ui-autocomplete-input')) {
				$this.autocomplete({
					source: <?php echo json_encode( $record_link_source_names ); ?>,
					minLength: 0
				});
			}
		});
	});
	</script>
	
	<style type="text/css">
	.ui-autocomplete { overflow-y: auto; overflow-x: hidden; max-height: 180px;}
	
	#audiotheme-record-types { margin: 1em 0;}
	#audiotheme-record-types li { margin: 0; vertical-align: middle;}
	#audiotheme-record-types li input { vertical-align: middle;}
	#audiotheme-record-types li label { margin: 0; font-weight: normal;}
	#audiotheme-record-types ul { margin-top: 3px; margin-bottom: 0;}
		
	#record-links { width: 100%; border-spacing: 0;}
	#record-links td { padding: 0 0 5px 0;}
	#record-links th { text-align: left;}
	#record-links tfoot td { padding-top: 5px; }
	#record-links tfoot td a { float: right }
	#record-links tfoot td .meta-repeater-sort-warning { color: red }
	#record-links .column-action { padding: 0 0 0 5px;}
	</style>
	<?php
}
?>