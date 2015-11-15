<?php
/**
 * Gig-related admin functionality.
 *
 * @package AudioTheme\Gigs
 */

/**
 * Include gig admin dependencies.
 */
require( AUDIOTHEME_DIR . 'modules/gigs/admin/ajax.php' );

/**
 * Attach hooks for loading and managing gigs in the admin dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_admin_setup() {
	global $pagenow;

	add_action( 'save_post_audiotheme_gig', 'audiotheme_gig_save_post', 10, 2 );
	add_action( 'save_post_audiotheme_venue', 'audiotheme_venue_save_post', 10, 2 );

	add_action( 'admin_menu', 'audiotheme_gigs_admin_menu' );
	add_filter( 'post_updated_messages', 'audiotheme_gig_post_updated_messages' );

	// Register ajax admin actions.
	add_action( 'wp_ajax_audiotheme_ajax_get_venue_matches', 'audiotheme_ajax_get_venue_matches' );
	add_action( 'wp_ajax_audiotheme_ajax_is_new_venue',      'audiotheme_ajax_is_new_venue' );
	add_action( 'wp_ajax_audiotheme_ajax_get_venue',         'audiotheme_ajax_get_venue' );
	add_action( 'wp_ajax_audiotheme_ajax_get_venues',        'audiotheme_ajax_get_venues' );
	add_action( 'wp_ajax_audiotheme_ajax_save_venue',        'audiotheme_ajax_save_venue' );

	// Register assets.
	$post_type_object = get_post_type_object( 'audiotheme_venue' );
	$base_url = set_url_scheme( AUDIOTHEME_URI . 'modules/gigs/admin' );

	wp_register_script( 'audiotheme-gig-edit', $base_url . '/js/gig-edit.bundle.min.js', array( 'audiotheme-admin', 'audiotheme-venue-manager', 'jquery-timepicker', 'jquery-ui-autocomplete', 'pikaday', 'underscore', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );
	wp_register_script( 'audiotheme-venue-edit', $base_url . '/js/venue-edit.bundle.min.js', array( 'audiotheme-admin', 'jquery-ui-autocomplete', 'post', 'underscore' ), AUDIOTHEME_VERSION, true );
	wp_register_script( 'audiotheme-venue-manager', $base_url . '/js/venue-manager.bundle.min.js', array( 'audiotheme-admin', 'jquery', 'jquery-ui-autocomplete', 'media-models', 'media-views', 'underscore', 'wp-backbone', 'wp-util' ), AUDIOTHEME_VERSION, true );
	wp_register_style( 'audiotheme-venue-manager', AUDIOTHEME_URI . 'admin/css/venue-manager.min.css', array(), '1.0.0' );

	$settings = array(
		'canPublishVenues'      => false,
		'canEditVenues'         => current_user_can( $post_type_object->cap->edit_posts ),
		'defaultTimezoneString' => get_option( 'timezone_string' ),
		'insertVenueNonce'      => false,
		'l10n'                  => array(
			'addNewVenue'  => $post_type_object->labels->add_new_item,
			'addVenue'     => esc_html__( 'Add a Venue', 'audiotheme' ),
			'edit'         => esc_html__( 'Edit', 'audiotheme' ),
			'manageVenues' => esc_html__( 'Select Venue', 'audiotheme' ),
			'select'       => esc_html__( 'Select', 'audiotheme' ),
			'selectVenue'  => esc_html__( 'Select Venue', 'audiotheme' ),
			'venues'       => $post_type_object->labels->name,
			'view'         => esc_html__( 'View', 'audiotheme' ),
		),
	);

	if ( current_user_can( $post_type_object->cap->publish_posts ) ) {
		$settings['canPublishVenues'] = true;
		$settings['insertVenueNonce'] = wp_create_nonce( 'insert-venue' );
	}

	wp_localize_script( 'audiotheme-venue-manager', '_audiothemeVenueManagerSettings', $settings );
}

/**
 * Add the admin menu items for gigs.
 *
 * @since 1.0.0
 */
function audiotheme_gigs_admin_menu() {
	$screen = new AudioTheme_Screen_ManageGigs();
	$screen->register_hooks();

	add_action( 'load-post.php', 'audiotheme_gig_edit_screen_setup' );
	add_action( 'load-post-new.php', 'audiotheme_gig_edit_screen_setup' );
	add_action( 'load-post.php', 'audiotheme_venue_edit_screen_setup' );
	add_action( 'load-post-new.php', 'audiotheme_venue_edit_screen_setup' );
}

/**
 * Gig update messages.
 *
 * @see /wp-admin/edit-form-advanced.php
 *
 * @param array $messages The array of post update messages.
 * @return array
 */
function audiotheme_gig_post_updated_messages( $messages ) {
	global $post;

	$messages['audiotheme_gig'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Gig updated. <a href="%s">View Gig</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		2  => __( 'Custom field updated.', 'audiotheme' ),
		3  => __( 'Custom field deleted.', 'audiotheme' ),
		4  => __( 'Gig updated.', 'audiotheme' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Gig restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => sprintf( __( 'Gig published. <a href="%s">View Gig</a>', 'audiotheme' ), esc_url( get_permalink( $post->ID ) ) ),
		7  => __( 'Gig saved.', 'audiotheme' ),
		8  => sprintf( __( 'Gig submitted. <a target="_blank" href="%s">Preview Gig</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
		9  => sprintf( __( 'Gig scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Gig</a>', 'audiotheme' ),
			/* translators: Publish box date format, see http://php.net/date */
		date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
		10 => sprintf( __( 'Gig draft updated. <a target="_blank" href="%s">Preview Gig</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) ),
	);

	return $messages;
}

/**
 * Set up the gig Add/Edit screen.
 *
 * Add custom meta boxes, enqueues scripts and styles, and hook up the action
 * to display the edit fields after the title.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post The gig post object being edited.
 */
function audiotheme_gig_edit_screen_setup( $post ) {
	if ( 'audiotheme_gig' !== get_current_screen()->id ) {
		return;
	}

	add_action( 'add_meta_boxes', 'audiotheme_gig_edit_meta_boxes' );
	add_action( 'admin_enqueue_scripts', 'audiotheme_gig_edit_assets' );
	add_action( 'edit_form_after_title', 'audiotheme_edit_gig_fields' );
	add_action( 'admin_footer', 'audiotheme_edit_gig_print_templates' );
}

/**
 * Register meta boxes for the Edit Gig screen.
 *
 * @since 1.9.0
 */
function audiotheme_gig_edit_meta_boxes() {
	// Add a customized submit meta box.
	remove_meta_box( 'submitdiv', 'audiotheme_gig', 'side' );

	add_meta_box(
		'submitdiv',
		__( 'Publish', 'audiotheme' ),
		'audiotheme_post_submit_meta_box',
		'audiotheme_gig',
		'side',
		'high',
		array(
			'force_delete'      => false,
			'show_publish_date' => false,
			'show_statuses'     => array(),
			'show_visibility'   => false,
		)
	);

	// Add a meta box for entering ticket information.
	add_meta_box(
		'audiothemegigticketsdiv',
		__( 'Tickets', 'audiotheme' ),
		'audiotheme_gig_tickets_meta_box',
		'audiotheme_gig',
		'side',
		'default'
	);
}

/**
 * Enqueue assets for the Edit Gig screen.
 *
 * @since 1.9.0
 */
function audiotheme_gig_edit_assets() {
	wp_enqueue_script( 'audiotheme-gig-edit' );
	wp_enqueue_style( 'audiotheme-admin' );
	wp_enqueue_style( 'audiotheme-venue-manager' );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
}

/**
 * Setup and display the main gig fields for editing.
 *
 * @since 1.0.0
 */
function audiotheme_edit_gig_fields() {
	global $post, $wpdb;

	$gig = get_audiotheme_gig( $post->ID );

	$gig_date  = '';
	$gig_time  = '';

	if ( $gig->gig_datetime ) {
		$timestamp = strtotime( $gig->gig_datetime );
		// jQuery date format is kinda limited?
		$gig_date = date( 'Y/m/d', $timestamp );

		$t = date_parse( $gig->gig_time );
		if ( empty( $t['errors'] ) ) {
			$gig_time = date( audiotheme_compatible_time_format(), $timestamp );
		}
	}

	$gig_venue       = isset( $gig->venue->name ) ? $gig->venue->name : '';
	$timezone_string = isset( $gig->venue->timezone_string ) ? $gig->venue->timezone_string : '';
	$venue_id        = isset( $gig->venue->ID ) ? $gig->venue->ID : 0;

	wp_localize_script( 'audiotheme-gig-edit', '_audiothemeGigEditSettings', array(
		'venue'      => prepare_audiotheme_venue_for_js( $venue_id ),
		'timeFormat' => audiotheme_compatible_time_format(),
	) );

	require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/edit-gig.php' );
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
	<p class="audiotheme-field">
		<label for="gig-tickets-price">Price:</label><br>
		<input type="text" name="gig_tickets_price" id="gig-tickets-price" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_price', true ) ); ?>" class="large-text">
	</p>
	<p class="audiotheme-field">
		<label for="gig-tickets-url">Tickets URL:</label><br>
		<input type="text" name="gig_tickets_url" id="gig-tickets-url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_audiotheme_tickets_url', true ) ); ?>" class="large-text">
	</p>
	<?php
}

/**
 * Print Underscore.js templates.
 *
 * @since 1.9.0
 */
function audiotheme_edit_gig_print_templates() {
	include( AUDIOTHEME_DIR . 'modules/gigs/admin/views/templates-gig.php' );
	include( AUDIOTHEME_DIR . 'modules/gigs/admin/views/templates-venue.php' );
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
	$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$is_revision    = wp_is_post_revision( $post_id );
	$is_valid_nonce = isset( $_POST['audiotheme_save_gig_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_save_gig_nonce'], 'save-gig_' . $post_id );

	// Bail if the data shouldn't be saved or intention can't be verified.
	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	$post_type_object = get_post_type_object( 'audiotheme_gig' );
	if ( isset( $_POST['gig_date'] ) && isset( $_POST['gig_time'] ) && current_user_can( $post_type_object->cap->edit_post, $post_id ) ) {
		set_audiotheme_gig_venue_id( $post_id, $_POST['gig_venue_id'] );

		$dt = date_parse( $_POST['gig_date'] . ' ' . $_POST['gig_time'] );

		// Date and time are always stored local to the venue.
		// If GMT, or time in another locale is needed, use the venue time zone to calculate.
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
					'ID'        => $post->ID,
					'post_name' => sprintf( '%s-%s-%s', $dt['year'], zeroise( $dt['month'], 2 ), zeroise( $dt['day'], 2 ) ),
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
