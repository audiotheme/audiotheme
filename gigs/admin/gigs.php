<?php
add_action( 'init', 'audiotheme_load_gigs_admin' );

function audiotheme_load_gigs_admin() {
	if ( isset( $_POST['audiotheme_gigs_rewrite_base'] ) ) {
		update_option( 'audiotheme_gigs_rewrite_base', $_POST['audiotheme_gigs_rewrite_base'] );
	}
	
	add_action( 'admin_menu', 'audiotheme_gigs_admin_menu' );
	add_action( 'admin_init', 'audiotheme_gigs_admin_init' );
	add_filter( 'set-screen-option', 'audiotheme_gigs_screen_options', 10, 3 );
	add_action( 'save_post', 'audiotheme_gig_save_hook' );
	add_filter( 'get_edit_post_link', 'get_audiotheme_venue_edit_link', 10, 2 );
	
	add_action( 'before_delete_post', 'audiotheme_gig_before_delete_hook' );
}

function audiotheme_gigs_admin_menu() {
	global $pagenow, $plugin_page, $typenow;
	
	// redirect the default All Gigs screen
	if ( 'audiotheme_gig' == $typenow && 'edit.php' == $pagenow ) {
		wp_redirect( get_audiotheme_gig_admin_url() );
		exit;
	}
	
	
	$gig_object = get_post_type_object( 'audiotheme_gig' );
	$venue_object = get_post_type_object( 'audiotheme_venue' );
	
	remove_submenu_page( 'gigs', 'edit.php?post_type=audiotheme_gig' );
	
	$all_gigs_hook = add_menu_page( $gig_object->labels->name, $gig_object->labels->menu_name, 'edit_posts', 'gigs', 'audiotheme_all_gigs_screen', NULL, 6 );
		add_submenu_page( 'gigs', $gig_object->labels->name, $gig_object->labels->all_items, 'edit_posts', 'gigs', 'audiotheme_all_gigs_screen' );
		$edit_gig_hook = add_submenu_page( 'gigs', $gig_object->labels->add_new_item, $gig_object->labels->add_new_item, 'edit_posts', 'post-new.php?post_type=audiotheme_gig' );
		$all_venues_hook = add_submenu_page( 'gigs', $venue_object->labels->name, $venue_object->labels->menu_name, 'edit_posts', 'venues', 'audiotheme_all_venues_screen' );
		$edit_venue_hook = add_submenu_page( 'gigs', $venue_object->labels->add_new_item, $venue_object->labels->add_new_item, 'edit_posts', 'venue', 'audiotheme_edit_venue_screen' );
	
	add_action( 'parent_file', 'audiotheme_gigs_admin_menu_highlight' );
	add_action( 'load-' . $all_gigs_hook, 'audiotheme_all_gigs_screen_setup' );
	add_action( 'load-' . $edit_gig_hook, 'audiotheme_edit_gig_screen_setup' );
	add_action( 'load-' . $all_venues_hook, 'audiotheme_all_venues_screen_setup' );
	add_action( 'load-' . $edit_venue_hook, 'audiotheme_edit_venue_screen_setup' );
	
	/*$current_page_hook = get_plugin_page_hook( $plugin_page, $pagenow );
	if ( $current_page_hook == $all_hook ) {
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	}*/
}

function audiotheme_gigs_screen_options( $return, $option, $value ) {
	if ( 'toplevel_page_gigs_per_page' == $option || 'gigs_page_venues_per_page' == $option ) {
		$return = absint( $value );
	}
	
	return $return;
}

function audiotheme_gigs_admin_menu_highlight( $parent_file ) {
	global $pagenow, $post_type, $submenu, $submenu_file;
	
	if ( 'audiotheme_gig' == $post_type ) {
		$parent_file = 'gigs';
		$submenu_file = ( 'post.php' == $pagenow ) ? 'gigs' : $submenu_file;
	}
	
	if ( 'gigs' == $parent_file && isset( $_GET['page'] ) && 'venue' == $_GET['page']/* && isset( $_GET['action'] ) && 'edit' == $_GET['action']*/ ) {
		$submenu_file = 'venues';
	}
	
	// remove the Add New Venue submenu item
	if ( isset( $submenu['gigs'] ) ) {
		foreach ( $submenu['gigs'] as $key => $sm ) {
			if ( isset( $sm[0] ) && 'Add New Venue' == $sm[0] ) {
				unset( $submenu['gigs'][ $key ] );
			}
		}
	}
	
	return $parent_file;
}

function audiotheme_all_gigs_screen_setup() {
	get_current_screen()->add_help_tab( array(
		'id' => 'overview',
		'title' => __( 'Overview', 'audiotheme-i18n' ),
		'content' => '<p>' . __( 'This screen provides access to all of your gigs. You can customize the display of this screen to suit your workflow.', 'audiotheme-i18n' ) . '</p>'
	) );
	
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	$title = $post_type_object->labels->name;
	add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );
	
	
	require_once( AUDIOTHEME_DIR . 'gigs/admin/includes/class-audiotheme-gigs-list-table.php' );
	
	$gigs_list_table = new AudioTheme_Gigs_List_Table();
	$gigs_list_table->process_actions();
}

function audiotheme_all_gigs_screen() {
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	
	$gigs_list_table = new AudioTheme_Gigs_List_Table();
	$gigs_list_table->prepare_items();
	
	require( AUDIOTHEME_DIR . 'gigs/admin/views/list-gigs.php' );
}


/**
 * Add New & Edit Screen
 */
function audiotheme_edit_gig_meta_boxes( $post ) {
	wp_dequeue_script( 'autosave' );
	
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-timepicker', AUDIOTHEME_URI . 'includes/js/jquery.timepicker.js', array( 'jquery' ) );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	
	remove_meta_box( 'submitdiv', 'audiotheme_gig', 'side' );
	add_meta_box( 'submitdiv', 'Publish', 'audiotheme_post_submit_meta_box', 'audiotheme_gig', 'side', 'high', array(
		'force_delete' => false,
		'show_publish_date' => false,
		'show_statuses' => array(),
		'show_visibility' => false
	) );
	
	add_action( 'edit_form_advanced', 'audiotheme_edit_gig_fields' );
}

function audiotheme_edit_gig_fields() {
	global $post, $wpdb;
	
	$gig = get_audiotheme_gig( $post->ID );
	
	$gig_date = '';
	$gig_time = '';
	$gig_venue = '';
	
	if ( $gig->gig_datetime ) {
		$timestamp = strtotime( $gig->gig_datetime );
		// jQuery date format is kinda limited
		$gig_date = date( 'm/d/Y', $timestamp );
		
		$t = date_parse( $gig->gig_time );
		if ( empty( $t['errors'] ) ) {
			$gig_time = date( get_option( 'time_format' ), $timestamp );
		} else {
			// no values allowed other than valid times
			// empty value defaults to TBA
			// TODO: make TBA an option or i18n
			$gig_time = '';
		}
	}
	
	$gig_venue = ( isset( $gig->venue->name ) ) ? $gig->venue->name : '';
	
	require( AUDIOTHEME_DIR . 'gigs/admin/views/edit-gig.php' );
}


/**
 * Update Venue Gig Count on Gig Delete
 *
 * Determines if a venue's gig_count meta field needs to be updated
 * when a gig is deleted.
 *
 * @since 1.0
 */
function audiotheme_gig_before_delete_hook( $post_id ) {
	if ( 'audiotheme_gig' == get_post_type( $post_id ) ) {
		$gig = get_audiotheme_gig( $post_id );
		if ( isset( $gig->venue->ID ) ) {
			$count = get_audiotheme_venue_gig_count( $gig->venue->ID );
			update_audiotheme_venue_gig_count( $gig->venue->ID, --$count );
		}
	}
}

/**
 * Process and save gig info when an audiotheme_gig CPT is saved
 *
 * @since 1.0
 */
function audiotheme_gig_save_hook( $gig_id ) {
	global $wpdb;
	
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $gig_id ) )
		return;
	
	// TODO: verify nonce
	#if( ! isset( $_POST['audiotheme_gig_nonce'] ) || ! wp_verify_nonce( $_POST['audiotheme_gig_nonce'], 'save-gig-meta' ) )
		#return;
	
	if ( 'audiotheme_gig' != get_post_type( $gig_id ) )
		return false;
	
	
	if ( isset( $_POST['gig_date'] ) && isset( $_POST['gig_time'] ) && current_user_can( 'edit_post' ) ) {
		$venue = set_audiotheme_gig_venue( $gig_id, $_POST['gig_venue'] );
		
		// TODO: return error if invalid date
		$dt = date_parse( $_POST['gig_date'] . ' ' . $_POST['gig_time'] );
		
		// Date and time are always stored local to the venue
		// If GMT, or time in another locale is needed, use the venue timezone to calculate
		// Other functions should be aware that time is optional; check for the presence of gig_time
		if ( checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) {
			$datetime = sprintf( '%d-%s-%s %s:%s:%s',
				$dt['year'],
				zeroise( $dt['month'], 2 ),
				zeroise( $dt['day'], 2 ),
				zeroise( $dt['hour'], 2 ),
				zeroise( $dt['minute'], 2 ),
				zeroise( $dt['second'], 2 ) );
			
			update_post_meta( $gig_id, 'gig_datetime', $datetime );
			
			// If the post name is empty, default it to the date
			$post = get_post( $gig_id );
			if ( empty( $post->post_name ) ) {
				wp_update_post( array(
					'ID' => $post->ID,
					'post_name' => sprintf( '%s-%s-%s', $dt['year'], zeroise( $dt['month'], 2 ), zeroise( $dt['day'], 2 ) )
				) );
			}
		} else {
			update_post_meta( $gig_id, 'gig_datetime', '' );
		}
		
		// Store time separately to check for empty values, TBA, etc.
		$time = $_POST['gig_time'];
		$t = date_parse( $time );
		if ( empty( $t['errors'] ) ) {
			$time = sprintf( '%s:%s:%s',
				zeroise( $t['hour'], 2 ),
				zeroise( $t['minute'], 2 ),
				zeroise( $t['second'], 2 ) );
		}
		update_post_meta( $gig_id, 'gig_time', $time );
	}
}

/**
 * Register Gigs Rewrite Base Setting
 *
 * @since 1.0
 */
function audiotheme_gigs_admin_init() {
	add_settings_field(
		'audiotheme_gigs_rewrite_base',
		'<label for="audiotheme-gigs-rewrite-base">' . __( 'Gigs base', 'audiotheme-i18n' ) . '</label>',
		'audiotheme_gigs_rewrite_base_settings_field',
		'permalink',
		'optional'
	);
}

/**
 * Callback for Displaying Gigs Rewrite Base
 *
 * @since 1.0
 */
function audiotheme_gigs_rewrite_base_settings_field() {
	$gigs_base = get_option( 'audiotheme_gigs_rewrite_base' );
	?>
	<input type="text" name="audiotheme_gigs_rewrite_base" id="audiotheme-gigs-rewrite-base" value="<?php echo esc_attr( $gigs_base ); ?>" class="regular-text code">
	<span class="description"><?php _e( 'Default is <code>shows</code>.', 'audiotheme-i18n' ); ?></span>
	<?php
}
?>