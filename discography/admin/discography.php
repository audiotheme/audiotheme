<?php
add_action( 'init', 'audiotheme_load_discography_admin' );
function audiotheme_load_discography_admin() {
	if ( ! empty( $_POST ) ) {
		#vd( $_POST );
		#exit;
	}
	
	if ( isset( $_POST['audiotheme_discography_rewrite_base'] ) ) {
		update_option( 'audiotheme_discography_rewrite_base', $_POST['audiotheme_discography_rewrite_base'] );
	}
	
	add_action( 'save_post', 'audiotheme_record_save_hook' );
	add_action( 'admin_init', 'audiotheme_discography_admin_init' );
}

function audiotheme_record_save_hook( $post_id ) {
	// Let's not auto save the data
	if( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) )
		return; 

	// Check our nonce
	#if( ! isset( $_POST['audiotheme_record_nonce'] ) || ! wp_verify_nonce( $_POST['audiotheme_record_nonce'], 'save_audiotheme_record_meta' ) )
		#return;
	
	if ( 'audiotheme_record' != get_post_type( $post_id ) )
		return false;

	// Make sure the current user can edit the post
	if( ! current_user_can( 'edit_post' ) )
		return;
	
	$current_user = wp_get_current_user();
	
	update_post_meta( $post_id, '_release_year', $_POST['release_year'] );
	update_post_meta( $post_id, '_genre', $_POST['genre'] );
	update_post_meta( $post_id, '_purchase_url', $_POST['purchase_url'] );
	
	if ( ! empty( $_POST['audiotheme_tracks'] ) ) {
		$i = 0;
		foreach ( $_POST['audiotheme_tracks'] as $track_data ) {
			$default_data = array( 'artist' => '', 'post_id' => '', 'title' => '' );
			$track_data = wp_parse_args( $track_data, $default_data );
			
			if ( ! empty( $track_data['title'] ) ) {
				$data = array();
				$track_id = 0;
				
				$data['post_title'] = $track_data['title'];
				$data['post_status'] = 'publish';
				$data['post_parent'] = $post_id;
				$data['menu_order'] = $i;
				$data['post_type'] = 'audiotheme_track';
				
				if ( empty( $track_data['post_id'] ) ) {
					$track_id = wp_insert_post( $data );
				} else {
					$track_id = absint( $track_data['post_id'] );
					$data['ID'] = $track_id;
					$data['post_author'] = $current_user->ID;
					
					wp_update_post( $data );
				}
				
				if ( ! empty( $track_id ) && ! is_wp_error( $track_id ) ) {
					update_post_meta( $track_id, '_artist', $track_data['artist'] );
				}
				
				$i++;
			} elseif ( ! empty( $track_data['post_id'] ) ) {
				update( absint( $track_data['post_id'] ), '_artist', $track_data['artist'] );
			}
		}
	}
}

function audiotheme_edit_record_meta_boxes( $post ) {
	remove_meta_box( 'submitdiv', 'audiotheme_record', 'side' );
	add_meta_box( 'submitdiv', 'Publish', 'audiotheme_post_submit_meta_box', 'audiotheme_record', 'side', 'high', array(
		'force_delete' => false,
		'show_publish_date' => false,
		'show_statuses' => array(),
		'show_visibility' => false
	) );
	
	add_meta_box( 'audiotheme-record-details', __( 'Record Details', 'audiotheme-i18n' ), 'audiotheme_record_details_meta_box', 'audiotheme_record', 'side', 'high' );
	
	add_action( 'edit_form_advanced', 'audiotheme_edit_record_tracklist' );
}

function audiotheme_edit_record_tracklist() {
	global $post, $wpdb;
	
	$tracks = get_posts( 'post_type=audiotheme_track&post_parent=' . $post->ID . '&orderby=menu_order&order=ASC&numberposts=-1' );
	
	require( AUDIOTHEME_DIR . 'discography/admin/views/edit-record-tracklist.php' );
}

function audiotheme_record_details_meta_box( $post ) {
	?>
	<p>
		<label for="record-year">Release Year:</label>
		<input type="text" name="release_year" id="record-year" value="<?php echo esc_attr( get_post_meta( $post->ID, '_release_year', true ) ) ; ?>" class="widefat">
	</p>
	<p>
		<label for="record-genre">Genre:</label>
		<input type="text" name="genre" id="record-genere" value="<?php echo esc_attr( get_post_meta( $post->ID, '_genre', true ) ) ; ?>" class="widefat">
	</p>
	<p>
		<label for="record-purchase-url">Purchase URL:</label>
		<input type="url" name="purchase_url" id="record-purchase-url" value="<?php echo esc_url( get_post_meta( $post->ID, '_purchase_url', true ) ) ; ?>" class="widefat">
	</p>
	<?php
}




function audiotheme_discography_admin_init() {
	add_settings_field(
		'audiotheme_discography_rewrite_base',
		'<label for="audiotheme-discography-rewrite-base">' . __( 'Discography base', 'audiotheme-i18n' ) . '</label>',
		'audiotheme_discography_rewrite_base_settings_field',
		'permalink',
		'optional'
	);
}

function audiotheme_discography_rewrite_base_settings_field() {
	$discography_base = get_option( 'audiotheme_discography_rewrite_base' );
	?>
	<input type="text" name="audiotheme_discography_rewrite_base" id="audiotheme-discography-rewrite-base" value="<?php echo esc_attr( $discography_base ); ?>" class="regular-text code">
	<span class="description"><?php _e( 'Default is <code>record</code>.', 'audiotheme-i18n' ); ?></span>
	<?php
}
?>