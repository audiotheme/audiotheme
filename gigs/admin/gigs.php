<?php
add_action( 'init', 'audiotheme_load_gigs_admin' );

function audiotheme_load_gigs_admin() {
	add_action( 'admin_menu', 'audiotheme_gigs_admin_menu' );
	add_filter( 'set-screen-option', 'audiotheme_gigs_screen_options', 10, 3 );
	add_action( 'save_post', 'audiotheme_gig_save_hook' );
	add_filter( 'get_edit_post_link', 'get_audiotheme_venue_edit_link', 10, 2 );
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
		'title' => __( 'Overview' ),
		'content' =>
			'<p>' . __( 'This screen provides access to all of your gigs. You can customize the display of this screen to suit your workflow.' ) . '</p>'
	) );
	
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	$title = $post_type_object->labels->name;
	add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );
	
	
	include_once AUDIOTHEME_DIR . 'gigs/admin/includes/class-audiotheme-gigs-list-table.php';
	
	$gigs_list_table = new AudioTheme_Gigs_List_Table();
	$gigs_list_table->process_actions();
}

function audiotheme_all_gigs_screen() {
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	
	$gigs_list_table = new AudioTheme_Gigs_List_Table();
	$gigs_list_table->prepare_items();
	
	include AUDIOTHEME_DIR . 'gigs/admin/views/list-gigs.php';
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
	
	include AUDIOTHEME_DIR . 'gigs/admin/views/edit-gig.php';
}


/**
 * Process and save gig info when an audiotheme_gig CPT is saved
 *
 * 
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
		// TODO: return error if invalid date
		$dt = date_parse( $_POST['gig_date'] . ' ' . $_POST['gig_time'] );
		
		if ( checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) {
			$datetime = sprintf( '%d-%s-%s %s:%s:%s',
				$dt['year'],
				zeroise( $dt['month'], 2 ),
				zeroise( $dt['day'], 2 ),
				zeroise( $dt['hour'], 2 ),
				zeroise( $dt['minute'], 2 ),
				zeroise( $dt['second'], 2 ) );
			update_post_meta( $gig_id, 'gig_datetime', $datetime );
		} else {
			update_post_meta( $gig_id, 'gig_datetime', '' );
		}
		
		// store time separately to check for empty values, TBA, etc.
		$time = $_POST['gig_time'];
		$t = date_parse( $time );
		if ( empty( $t['errors'] ) ) {
			$time = sprintf( '%s:%s:%s',
				zeroise( $t['hour'], 2 ),
				zeroise( $t['minute'], 2 ),
				zeroise( $t['second'], 2 ) );
		}
		update_post_meta( $gig_id, 'gig_time', $time );
		
		set_audiotheme_gig_venue( $gig_id, $_POST['gig_venue'] );
	}
}
?>