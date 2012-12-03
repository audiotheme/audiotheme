<?php
/**
 * Gig-related functionality in the admin dashboard.
 *
 * @package AudioTheme_Framework
 * @subpackage Gigs
 */

/**
 * Include gig admin dependencies.
 */
require( AUDIOTHEME_DIR . 'gigs/admin/includes/admin-ajax.php' );

/**
 * Load gigs admin on init.
 */
add_action( 'init', 'audiotheme_gigs_admin_setup' );

/**
 * Attach hooks for loading and managing gigs in the admin dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_admin_setup() {
	global $pagenow;
	
	// Update the gig rewrite base.
	if ( isset( $_POST['audiotheme_gigs_rewrite_base_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_gigs_rewrite_base_nonce'], 'save-gigs-rewrite-base' ) ) {
		update_option( 'audiotheme_gigs_rewrite_base', $_POST['audiotheme_gigs_rewrite_base'] );
	}
	
	// @todo Move these so they're always registered, not just in the admin.
	add_action( 'save_post', 'audiotheme_gig_save_post', 10, 2 );
	add_filter( 'get_edit_post_link', 'get_audiotheme_venue_edit_link', 10, 2 );
	add_action( 'before_delete_post', 'audiotheme_gig_before_delete' );
	
	add_action( 'admin_menu', 'audiotheme_gigs_admin_menu' );
	add_action( 'admin_init', 'audiotheme_gigs_admin_init' );
	add_filter( 'audiotheme_nav_menu_archive_items', 'audiotheme_gigs_archive_menu_item' );
	
	// Register ajax admin actions.
	add_action( 'wp_ajax_ajax_get_audiotheme_venue_matches', 'ajax_get_audiotheme_venue_matches' );
	add_action( 'wp_ajax_ajax_is_new_audiotheme_venue', 'ajax_is_new_audiotheme_venue' );
	
	// Register scripts.
	wp_register_script( 'audiotheme-gig-edit', AUDIOTHEME_URI . 'gigs/admin/js/gig-edit.js', array( 'audiotheme-admin', 'jquery-timepicker', 'jquery-ui-autocomplete', 'jquery-ui-datepicker' ) );
	wp_localize_script( 'audiotheme-gig-edit', 'audiothemeGigsL10n', array(
		'datepickerIcon' => AUDIOTHEME_URI . 'admin/images/calendar.png',
		'timeFormat' => get_option( 'time_format' )
	) );
	
	wp_register_script( 'audiotheme-venue-edit', AUDIOTHEME_URI . 'gigs/admin/js/venue-edit.js', array( 'audiotheme-admin', 'jquery-ui-autocomplete', 'post' ) );
	
	// Only run on the gig and venue Manage Screens.
	if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && ( 'audiotheme-gigs' == $_GET['page'] || 'audiotheme-venues' == $_GET['page'] ) ) {
		add_filter( 'set-screen-option', 'audiotheme_gigs_screen_options', 999, 3 );
	}
}

/**
 * Add the admin menu items for gigs.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_admin_menu() {
	global $pagenow, $plugin_page, $typenow;
	
	// Redirect the default Manage Gigs screen.
	if ( 'audiotheme_gig' == $typenow && 'edit.php' == $pagenow ) {
		wp_redirect( get_audiotheme_gig_admin_url() );
		exit;
	}
	
	$gig_object = get_post_type_object( 'audiotheme_gig' );
	$venue_object = get_post_type_object( 'audiotheme_venue' );
	
	// Remove the default gigs menu item and replace it with the screen using the custom post list table.
	remove_submenu_page( 'audiotheme-gigs', 'edit.php?post_type=audiotheme_gig' );
	
	$manage_gigs_hook = add_menu_page( $gig_object->labels->name, $gig_object->labels->menu_name, 'edit_posts', 'audiotheme-gigs', 'audiotheme_gigs_manage_screen', null, 512 );
		add_submenu_page( 'audiotheme-gigs', $gig_object->labels->name, $gig_object->labels->all_items, 'edit_posts', 'audiotheme-gigs', 'audiotheme_gigs_manage_screen' );
		$edit_gig_hook = add_submenu_page( 'audiotheme-gigs', $gig_object->labels->add_new_item, $gig_object->labels->add_new_item, 'edit_posts', 'post-new.php?post_type=audiotheme_gig' );
		$manage_venues_hook = add_submenu_page( 'audiotheme-gigs', $venue_object->labels->name, $venue_object->labels->menu_name, 'edit_posts', 'audiotheme-venues', 'audiotheme_venues_manage_screen' );
		$edit_venue_hook = add_submenu_page( 'audiotheme-gigs', $venue_object->labels->add_new_item, $venue_object->labels->add_new_item, 'edit_posts', 'audiotheme-venue', 'audiotheme_venue_edit_screen' );
	
	add_action( 'parent_file', 'audiotheme_gigs_admin_menu_highlight' );
	add_action( 'load-' . $manage_gigs_hook, 'audiotheme_gigs_manage_screen_setup' );
	add_action( 'load-' . $edit_gig_hook, 'audiotheme_gig_edit_screen_setup' );
	add_action( 'load-' . $manage_venues_hook, 'audiotheme_venues_manage_screen_setup' );
	add_action( 'load-' . $edit_venue_hook, 'audiotheme_venue_edit_screen_setup' );
}

/**
 * Sanitize the 'per_page' screen option on the Manage Gigs and Manage Venues
 * screens.
 *
 * Apparently any other hook attached to the same filter that runs after this
 * will stomp all over it. To prevent this filter from doing the same, it's
 * only attached on the screens that require it. The priority should be set
 * extremely low to help ensure the correct value gets returned.
 *
 * @since 1.0.0
 *
 * @param bool $return Default is 'false'.
 * @param string $option The option name.
 * @param mixed $value The value to sanitize.
 * @return mixed The sanitized value.
 */
function audiotheme_gigs_screen_options( $return, $option, $value ) {
	if ( 'toplevel_page_audiotheme_gigs_per_page' == $option || 'gigs_page_audiotheme_venues_per_page' == $option ) {
		$return = absint( $value );
	}
	
	return $return;
}

/**
 * Higlight the correct top level and sub menu items for the gig screen being
 * displayed.
 *
 * @since 1.0.0
 * 
 * @param string $parent_file The screen being displayed.
 * @return string The menu item to highlight.
 */
function audiotheme_gigs_admin_menu_highlight( $parent_file ) {
	global $pagenow, $post_type, $submenu, $submenu_file;
	
	if ( 'audiotheme_gig' == $post_type ) {
		$parent_file = 'audiotheme-gigs';
		$submenu_file = ( 'post.php' == $pagenow ) ? 'audiotheme-gigs' : $submenu_file;
	}
	
	if ( 'audiotheme-gigs' == $parent_file && isset( $_GET['page'] ) && 'audiotheme-venue' == $_GET['page'] ) {
		$submenu_file = 'audiotheme-venues';
	}
	
	// Remove the Add New Venue submenu item.
	if ( isset( $submenu['audiotheme-gigs'] ) ) {
		foreach ( $submenu['audiotheme-gigs'] as $key => $sm ) {
			if ( isset( $sm[0] ) && 'audiotheme-venue' == $sm[2] ) {
				unset( $submenu['audiotheme-gigs'][ $key ] );
			}
		}
	}
	
	return $parent_file;
}

/**
 * Set up the gig Manage Screen.
 *
 * Adds a help tab, initializes the custom post list table, and processes any
 * actions that need to be handled.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_manage_screen_setup() {
	get_current_screen()->add_help_tab( array(
		'id'      => 'overview',
		'title'   => __( 'Overview', 'audiotheme-i18n' ),
		'content' => '<p>' . __( 'This screen provides access to all of your gigs. You can customize the display of this screen to suit your workflow.', 'audiotheme-i18n' ) . '</p>'
	) );
	
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	$title = $post_type_object->labels->name;
	add_screen_option( 'per_page', array( 'label' => $title, 'default' => 20 ) );
	
	require_once( AUDIOTHEME_DIR . 'gigs/admin/includes/class-audiotheme-gigs-list-table.php' );
	
	$gigs_list_table = new Audiotheme_Gigs_List_Table();
	$gigs_list_table->process_actions();
}

/**
 * Display the gig Manage Screen.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_manage_screen() {
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	
	$gigs_list_table = new Audiotheme_Gigs_List_Table();
	$gigs_list_table->prepare_items();
	
	require( AUDIOTHEME_DIR . 'gigs/admin/views/list-gigs.php' );
}

/**
 * Set up the gig Add/Edit screen.
 *
 * Add custom meta boxes, enqueues scripts and styles, and hook up the action
 * to display the edit fields after the title.
 *
 * @since 1.0.0
 * @todo Register the pointers in a central location so they're easier to edit?
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_edit_screen_setup( $post ) {
	get_current_screen()->add_help_tab( array(
		'id'      => 'customize',
		'title'   => __( 'Customize This Screen', 'audiotheme-i18n' ),
		'content' => '<p>' . __( 'The title field and the big Post Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes (Excerpt, Send Trackbacks, Custom Fields, Discussion, Slug, Author) or to choose a 1- or 2-column layout for this screen.', 'audiotheme-i18n' ) . '</p>'
	) );
	
	wp_enqueue_script( 'audiotheme-gig-edit' );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
	
	if ( ! is_audiotheme_pointer_dismissed( 'at100_gigvenue_tz' ) ) {
		wp_enqueue_script( 'wp-pointer' );
		wp_enqueue_style( 'wp-pointer' );
		
		$pointer  = 'Be sure to set a timezone when you add new venues so you don\'t have to worry about converting dates and times.' . "\n\n";
		$pointer .= 'It also gives your visitors the ability to subscribe to your events in their own timezones.' . "\n\n";
		// $pointer_content .= '<a href="">Find out more.</a>'; // Maybe link this to a help section?
		audiotheme_enqueue_pointer( 'at100_gigvenue_tz', 'Venue Timezones', $pointer, array( 'position' => 'top' ) );
	}
	
	// Add a customized submit meta box.
	remove_meta_box( 'submitdiv', 'audiotheme_gig', 'side' );
	add_meta_box( 'submitdiv', 'Publish', 'audiotheme_post_submit_meta_box', 'audiotheme_gig', 'side', 'high', array(
		'force_delete' => false,
		'show_publish_date' => false,
		'show_statuses' => array(),
		'show_visibility' => false
	) );
	
	// Add a meta box for entering ticket information.
	add_meta_box( 'audiothemegigticketsdiv', __( 'Tickets', 'audiotheme-i18n' ), 'audiotheme_gig_tickets_meta_box', 'audiotheme_gig', 'side', 'default' );
	
	// Display the main gig fields after the title.
	add_action( 'edit_form_after_title', 'audiotheme_edit_gig_fields' );
}

/**
 * Setup and display the main gig fields for editing.
 *
 * @since 1.0.0
 */
function audiotheme_edit_gig_fields() {
	global $post, $wpdb;
	
	$gig = get_audiotheme_gig( $post->ID );
	
	$gig_date = '';
	$gig_time = '';
	$gig_venue = '';
	
	if ( $gig->gig_datetime ) {
		$timestamp = strtotime( $gig->gig_datetime );
		// jQuery date format is kinda limited?
		$gig_date = date( 'm/d/Y', $timestamp );
		
		$t = date_parse( $gig->gig_time );
		if ( empty( $t['errors'] ) ) {
			$gig_time = date( get_option( 'time_format' ), $timestamp );
		} else {
			// No values allowed other than valid times.
			$gig_time = '';
		}
	}
	
	$gig_venue = ( isset( $gig->venue->name ) ) ? $gig->venue->name : '';
	
	require( AUDIOTHEME_DIR . 'gigs/admin/views/edit-gig.php' );
}

/**
 * Gig tickets meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_tickets_meta_box( $post ) {
	?>
	<p class="audiotheme-meta-field">
		<label for="gig-tickets-price">Price:</label><br>
		<input type="text" name="gig_tickets_price" id="gig-tickets-price" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_price', true ) ) ; ?>" class="large-text">
	</p>
	<p class="audiotheme-meta-field">
		<label for="gig-tickets-url">Tickets URL:</label><br>
		<input type="url" name="gig_tickets_url" id="gig-tickets-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_url', true ) ) ; ?>" class="large-text">
	</p>
	<?php
}

/**
 * Update a venue's cached gig count when gig is deleted.
 *
 * Determines if a venue's gig_count meta field needs to be updated
 * when a gig is deleted.
 *
 * @since 1.0.0
 *
 * @param int $post_id ID of the gig being deleted.
 */
function audiotheme_gig_before_delete( $post_id ) {
	if ( 'audiotheme_gig' == get_post_type( $post_id ) ) {
		$gig = get_audiotheme_gig( $post_id );
		if ( isset( $gig->venue->ID ) ) {
			$count = get_audiotheme_venue_gig_count( $gig->venue->ID );
			update_audiotheme_venue_gig_count( $gig->venue->ID, --$count );
		}
	}
}

/**
 * Process and save gig info when the CPT is saved.
 *
 * @since 1.0.0
 *
 * @param int $gig_id Gig post ID.
 * @param WP_Post $post Gig post object.
 */
function audiotheme_gig_save_post( $post_id, $post ) {
	$is_autosave = ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ? true : false;
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['audiotheme_save_gig_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_gig_nonce'], 'save-gig_' . $post_id ) ) ? true : false;
	
	// Bail if the data shouldn't be saved or intention can't be verified.
	if( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}
	
	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	if ( isset( $_POST['gig_date'] ) && isset( $_POST['gig_time'] ) && current_user_can( $post_type_object->cap->edit_post, $post_id ) ) {
		$venue = set_audiotheme_gig_venue( $post_id, $_POST['gig_venue'] );
		
		// @todo Return error if date is invalid.
		$dt = date_parse( $_POST['gig_date'] . ' ' . $_POST['gig_time'] );
		
		// Date and time are always stored local to the venue.
		// If GMT, or time in another locale is needed, use the venue timezone to calculate.
		// Other functions should be aware that time is optional; check for the presence of gig_time.
		if ( checkdate( $dt['month'], $dt['day'], $dt['year'] ) ) {
			$datetime = sprintf( '%d-%s-%s %s:%s:%s',
				$dt['year'],
				zeroise( $dt['month'], 2 ),
				zeroise( $dt['day'], 2 ),
				zeroise( $dt['hour'], 2 ),
				zeroise( $dt['minute'], 2 ),
				zeroise( $dt['second'], 2 ) );
			
			update_post_meta( $post_id, '_audiotheme_gig_datetime', $datetime );
			
			// If the post name is empty, default it to the date.
			if ( empty( $post->post_name ) ) {
				wp_update_post( array(
					'ID' => $post->ID,
					'post_name' => sprintf( '%s-%s-%s', $dt['year'], zeroise( $dt['month'], 2 ), zeroise( $dt['day'], 2 ) )
				) );
			}
		} else {
			update_post_meta( $post_id, '_audiotheme_gig_datetime', '' );
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

		update_post_meta( $post_id, '_audiotheme_gig_time', $time );
		update_post_meta( $post_id, '_audiotheme_tickets_price', $_POST['gig_tickets_price'] );
		update_post_meta( $post_id, '_audiotheme_tickets_url', $_POST['gig_tickets_url'] );
	}
}

/**
 * Register gigs rewrite base setting.
 *
 * @since 1.0.0
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
 * Callback for displaying the gigs rewrite base field.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_rewrite_base_settings_field() {
	$gigs_base = get_option( 'audiotheme_gigs_rewrite_base' );
	wp_nonce_field( 'save-gigs-rewrite-base', 'audiotheme_gigs_rewrite_base_nonce' );
	?>
	<input type="text" name="audiotheme_gigs_rewrite_base" id="audiotheme-gigs-rewrite-base" value="<?php echo esc_attr( $gigs_base ); ?>" class="regular-text code">
	<span class="description"><?php _e( 'Default is <code>shows</code>.', 'audiotheme-i18n' ); ?></span>
	<?php
}

/**
 * Add the gig archive link nav menu item to the custom AudioTheme Pages nav
 * menu meta box.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_archive_menu_item( $items ) {
	$items[] = array(
		'title' => _x( 'Gigs', 'nav menu archive label' ),
		'post_type' => 'audiotheme_gig',
		'url'   => get_post_type_archive_link( 'audiotheme_gig' )
	);
	
	return $items;
}
?>