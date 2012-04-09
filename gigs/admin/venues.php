<?php
function audiotheme_all_venues_screen_setup() {
	get_current_screen()->add_help_tab( array(
		'id' => 'overview',
		'title' => __( 'Overview' ),
		'content' =>
			'<p>' . __( 'This screen provides access to all of your venues. You can customize the display of this screen to suit your workflow.' ) . '</p>'
	) );
	
	$post_type_object = get_post_type_object( 'audiotheme_venue' );
	$title = $post_type_object->labels->name;
	add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );
	
	
	include AUDIOTHEME_DIR . 'gigs/admin/includes/class-audiotheme-venues-list-table.php';
	
	$venues_list_table = new AudioTheme_Venues_List_Table();
	$venues_list_table->process_actions();
	
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	
	#add_action( 'admin_notices', 'audiotheme_all_venues_notices' );
}

function audiotheme_all_venues_screen() {
	$venues_list_table = new AudioTheme_Venues_List_Table();
	$venues_list_table->prepare_items();
	
	
	$post_type_object = get_post_type_object( 'audiotheme_venue' );
	
	$action = 'add';
	$title = $post_type_object->labels->name;
	$nonce_field = wp_nonce_field( 'add-venue', 'audiotheme_venue_nonce', true, false );
	$values = get_default_audiotheme_venue_properties();
	
	if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
		$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );
		
		$action = 'edit';
		$nonce_field = wp_nonce_field( 'update-venue_' . $venue_to_edit->ID, 'audiotheme_venue_nonce', true, false );
		$values = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
	}
	
	extract( $values, EXTR_SKIP );
	
	include AUDIOTHEME_DIR . 'gigs/admin/views/list-venues.php';
}

function audiotheme_all_venues_notices() {
	
}


function audiotheme_edit_venue_screen_setup() {
	audiotheme_edit_venue_screen_process_actions();
	
	wp_enqueue_script( 'post' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	
	$values = get_default_audiotheme_venue_properties();
	if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
		$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );
		$values = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
	}
	
	add_meta_box( 'venuecontactdiv', 'Contact', 'audiotheme_edit_venue_contact_meta_box', 'gigs_page_venue', 'normal', 'core', $values );
	add_meta_box( 'venuenotesdiv', 'Notes', 'audiotheme_edit_venue_notes_meta_box', 'gigs_page_venue', 'normal', 'core', $values );
}

function audiotheme_edit_venue_screen() {
	$post_type_object = get_post_type_object( 'audiotheme_venue' );
	
	$action = 'add';
	$nonce_field = wp_nonce_field( 'add-venue', 'audiotheme_venue_nonce', true, false );
	$values = get_default_audiotheme_venue_properties();
	
	if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
		$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );
		
		$action = 'edit';
		$nonce_field = wp_nonce_field( 'update-venue_' . $venue_to_edit->ID, 'audiotheme_venue_nonce', true, false );
		$values = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
	}
	
	extract( $values, EXTR_SKIP );
	include AUDIOTHEME_DIR . 'gigs/admin/views/edit-venue.php';
}

function audiotheme_edit_venue_contact_meta_box( $post, $args ) {
	extract( $args['args'], EXTR_SKIP );
	include AUDIOTHEME_DIR . 'gigs/admin/views/edit-venue-contact.php';
}

function audiotheme_edit_venue_notes_meta_box( $post, $args ) {
	extract( $args['args'], EXTR_SKIP );
	
	$notes = format_to_edit( $notes, user_can_richedit() );
	wp_editor( $notes, 'venuenotes', array(
		'editor_css' => '<style type="text/css" scoped="true">.mceIframeContainer { background-color: #fff;}</style>',
		'media_buttons' => false,
		'textarea_name' => 'audiotheme_venue[notes]',
		'textarea_rows' => 6,
		'teeny' => true
	) );
}

function audiotheme_edit_venue_submit_meta_box( $post ) {
	$post = ( empty( $post ) ) ? get_default_post_to_edit( 'audiotheme_venue' ) : $post;
	
	// TODO: improve capabiity handling and cleanup
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$can_publish = current_user_can( $post_type_object->cap->publish_posts );
	?>
	<div class="submitbox" id="submitpost">
		
		<div id="major-publishing-actions">
			
			<?php if ( 'auto-draft' != $post->post_status && 'draft' != $post->post_status ) : ?>
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post ) ) {
						$delete_args['action'] = 'delete';
						$delete_args['venue_id'] = $post->ID;
						$delete_url = get_audiotheme_venues_admin_url( $delete_args );
						$delete_url_onclick = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
						echo sprintf( '<a href="%s" class="submitdelete deletion"%s>%s</a>', wp_nonce_url( $delete_url, 'delete-venue_' . $post->ID ), $delete_url_onclick, esc_html( __( 'Delete Permanently' ) ) );
					}
					?>
				</div>
			<?php endif; ?>
			
			<div id="publishing-action">
				<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="">
				<?php
				if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
					?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>">
					<?php
					submit_button( $post_type_object->labels->add_new_item, 'primary', 'publish', false, array( 'tabindex' => '5', 'accesskey' => 'p' ) );
				} else {
					?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>">
					<input type="submit" name="save" id="publish" class="button-primary" tabindex="5" accesskey="p" value="<?php esc_attr_e( 'Update' ) ?>">
				<?php } ?>
			</div><!--end div#publishing-action-->
			
			<div class="clear"></div>
		</div><!--end div#major-publishing-actions-->
	</div><!--end div#submitpost-->
	<?php
}

function audiotheme_edit_venue_screen_process_actions() {
	$action = '';
	if ( isset( $_POST['audiotheme_venue'] ) && isset( $_POST['audiotheme_venue_nonce'] ) ) {
		$data = $_POST['audiotheme_venue'];
		$nonce_action = ( empty( $data['ID'] ) ) ? 'add-venue' : 'update-venue_' . $data['ID'];
		
		// should die on error
		if ( check_admin_referer( $nonce_action, 'audiotheme_venue_nonce' ) ) {
			$action = ( ! empty( $data['ID'] ) ) ? 'edit' : 'add';
		}
	}
	
	// TODO: capability checks
	if ( ! empty( $action ) ) {
		$venue_id = save_audiotheme_venue( $data );
		$sendback = get_edit_post_link( $venue_id );
			
		if ( $venue_id && 'add' == $action ) {
			$sendback = add_query_arg( 'message', 1, $sendback );
		} elseif ( $venue_id && 'edit' == $action ) {
			$sendback = add_query_arg( 'message', 2, $sendback );
		} else {
			// TODO: return error message
		}
		
		wp_redirect( $sendback );
		exit;
	}
}




add_action( 'wp_ajax_ajax_get_audiotheme_venue_matches', 'ajax_get_audiotheme_venue_matches' );
function ajax_get_audiotheme_venue_matches() {
	global $wpdb;
	
	$var = like_escape( stripslashes( $_GET['name'] ) ) . '%';
	$venues = $wpdb->get_col( $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type='audiotheme_venue' AND post_title LIKE %s ORDER BY post_title ASC", $var ) );
	
	echo json_encode( $venues );
	exit;
}
?>