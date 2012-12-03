<?php
/**
 * Venue-related functionality in the admin dashboard.
 *
 * @package AudioTheme_Framework
 * @subpackage Gigs
 */

/**
 * Set up the venue Manage Screen.
 *
 * Adds a help tab and screen option, initializes the custom post list table,
 * and processes any actions that need to be handled.
 *
 * @since 1.0.0
 */
function audiotheme_venues_manage_screen_setup() {
	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'audiotheme-i18n' ),
		'content' => '<p>' . __( 'This screen provides access to all of your venues. You can customize the display of this screen to suit your workflow.', 'audiotheme-i18n' ) . '</p>'
	) );
	
	$post_type_object = get_post_type_object( 'audiotheme_venue' );
	$title = $post_type_object->labels->name;
	add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );
	
	require( AUDIOTHEME_DIR . 'gigs/admin/includes/class-audiotheme-venues-list-table.php' );
	
	$venues_list_table = new Audiotheme_Venues_List_Table();
	$venues_list_table->process_actions();
	
	#add_action( 'admin_notices', 'audiotheme_venues_manage_screen_notices' );
}

/**
 * Display the venue Manage Screen.
 *
 * @since 1.0.0
 */
function audiotheme_venues_manage_screen() {
	$venues_list_table = new Audiotheme_Venues_List_Table();
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
	
	require( AUDIOTHEME_DIR . 'gigs/admin/views/list-venues.php' );
}

/**
 *
 */
function audiotheme_venues_manage_screen_notices() {
	
}

/**
 * Set up the venue Add/Edit Screen.
 *
 * Add custom meta boxes, enqueue scripts and styles and process any actions.
 *
 * @since 1.0.0
 */
function audiotheme_venue_edit_screen_setup() {
	audiotheme_venue_edit_screen_process_actions();
	
	get_current_screen()->add_help_tab( array(
		'id'      => 'customize',
		'title'   => __( 'Customize This Screen', 'audiotheme-i18n' ),
		'content' => '<p>' . __( 'The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.', 'audiotheme-i18n' ) . '</p>'
	) );
	
	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __('For more information:') . '</strong></p>'
	);
	
	wp_enqueue_script( 'audiotheme-venue-edit' );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	
	$values = get_default_audiotheme_venue_properties();
	if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] && isset( $_GET['venue_id'] ) && is_numeric( $_GET['venue_id'] ) ) {
		$venue_to_edit = get_audiotheme_venue( $_GET['venue_id'] );
		$values = wp_parse_args( get_object_vars( $venue_to_edit ), $values );
	}
	
	add_meta_box( 'venuecontactdiv', 'Contact', 'audiotheme_venue_contact_meta_box', 'gigs_page_audiotheme-venue', 'normal', 'core', $values );
	add_meta_box( 'venuenotesdiv', 'Notes', 'audiotheme_venue_notes_meta_box', 'gigs_page_audiotheme-venue', 'normal', 'core', $values );
	
	// The 'submitdiv' id prevents the meta box from being hidden.
	add_meta_box( 'submitdiv', __( 'Save', 'audiotheme-i18n' ), 'audiotheme_venue_submit_meta_box', 'gigs_page_audiotheme-venue', 'side', 'high' );
}

/**
 * Process venue add/edit actions.
 *
 * @since 1.0.0
 */
function audiotheme_venue_edit_screen_process_actions() {
	$action = '';
	if ( isset( $_POST['audiotheme_venue'] ) && isset( $_POST['audiotheme_venue_nonce'] ) ) {
		$data = $_POST['audiotheme_venue'];
		$nonce_action = ( empty( $data['ID'] ) ) ? 'add-venue' : 'update-venue_' . $data['ID'];
		
		// Should die on error.
		if ( check_admin_referer( $nonce_action, 'audiotheme_venue_nonce' ) ) {
			$action = ( ! empty( $data['ID'] ) ) ? 'edit' : 'add';
		}
	}
	
	// @todo Capability checks.
	if ( ! empty( $action ) ) {
		$venue_id = save_audiotheme_venue( $data );
		$sendback = get_edit_post_link( $venue_id );
			
		if ( $venue_id && 'add' == $action ) {
			$sendback = add_query_arg( 'message', 1, $sendback );
		} elseif ( $venue_id && 'edit' == $action ) {
			$sendback = add_query_arg( 'message', 2, $sendback );
		} else {
			// @todo Return error message.
		}
		
		wp_redirect( $sendback );
		exit;
	}
}

/**
 * Display the venue Add/Edit screen.
 *
 * @since 1.0.0
 */
function audiotheme_venue_edit_screen() {
	$screen = get_current_screen();
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
	require( AUDIOTHEME_DIR . 'gigs/admin/views/edit-venue.php' );
}

/**
 * Display venue contact information meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Venue post object.
 * @param array $args Additional args passed during meta box registration.
 */
function audiotheme_venue_contact_meta_box( $post, $args ) {
	extract( $args['args'], EXTR_SKIP );
	require( AUDIOTHEME_DIR . 'gigs/admin/views/edit-venue-contact.php' );
}

/**
 * Display venue notes meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Venue post object.
 * @param array $args Additional args passed during meta box registration.
 */
function audiotheme_venue_notes_meta_box( $post, $args ) {
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

/**
 * Display custom venue submit meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_submit_meta_box( $post ) {
	$post = ( empty( $post ) ) ? get_default_post_to_edit( 'audiotheme_venue' ) : $post;
	
	// @todo Improve capability handling and clean up.
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
	$can_publish = current_user_can( $post_type_object->cap->publish_posts );
	?>
	<div class="submitbox" id="submitpost">
		
		<div id="major-publishing-actions">
			
			<?php if ( 'auto-draft' != $post->post_status && 'draft' != $post->post_status ) : ?>
				<div id="delete-action">
					<?php
					if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
						$delete_args['action'] = 'delete';
						$delete_args['venue_id'] = $post->ID;
						$delete_url = get_audiotheme_venues_admin_url( $delete_args );
						$delete_url_onclick = " onclick=\"return confirm('" . esc_js( sprintf( __( 'Are you sure you want to delete this %s?', 'audiotheme-i18n' ), strtolower( $post_type_object->labels->singular_name ) ) ) . "');\"";
						echo sprintf( '<a href="%s" class="submitdelete deletion"%s>%s</a>', wp_nonce_url( $delete_url, 'delete-venue_' . $post->ID ), $delete_url_onclick, esc_html( __( 'Delete Permanently', 'audiotheme-i18n' ) ) );
					}
					?>
				</div>
			<?php endif; ?>
			
			<div id="publishing-action">
				<?php audiotheme_admin_spinner( array( 'id' => 'ajax-loading' ) ); ?>
				<?php
				if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
					?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Publish' ) ?>">
					<?php
					submit_button( $post_type_object->labels->add_new_item, 'primary', 'publish', false, array( 'accesskey' => 'p' ) );
				} else {
					?>
					<input type="hidden" name="original_publish" id="original_publish" value="<?php esc_attr_e( 'Update' ) ?>">
					<input type="submit" name="save" id="publish" class="button-primary" accesskey="p" value="<?php esc_attr_e( 'Update' ) ?>">
				<?php } ?>
			</div><!--end div#publishing-action-->
			
			<div class="clear"></div>
		</div><!--end div#major-publishing-actions-->
	</div><!--end div#submitpost-->
	
	<script type="text/javascript">
	jQuery(function($) {
		$('input[type="submit"], a.submitdelete').click(function(){
			window.onbeforeunload = null;
			$(':button, :submit', '#submitpost').each(function(){
				var t = $(this);
				if ( t.hasClass('button-primary') )
					t.addClass('button-primary-disabled');
				else
					t.addClass('button-disabled');
			});
			
			if ( $(this).attr('id') == 'publish' )
				$('#major-publishing-actions .spinner').show();
		});
	});
	</script>
	<?php
}
?>