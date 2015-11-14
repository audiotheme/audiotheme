<?php
/**
 * Venue-related functionality in the admin dashboard.
 *
 * @package AudioTheme\Gigs
 */

/**
 * Set up the Manage Venues screen.
 *
 * @since 1.0.0
 */
function audiotheme_venues_manage_screen_setup() {
	$screen = new AudioTheme_Screen_ManageVenues();
	$screen->register_hooks();
}

/**
 * Set up the Edit Venue screen.
 *
 * @since 1.0.0
 */
function audiotheme_venue_edit_screen_setup() {
	if ( 'audiotheme_venue' !== get_current_screen()->id ) {
		return;
	}

	add_action( 'add_meta_boxes_audiotheme_venue', 'audiotheme_edit_venue_meta_boxes' );
	add_action( 'admin_enqueue_scripts', 'audiotheme_venue_edit_assets' );
	add_action( 'edit_form_after_title', 'audiotheme_venue_details_fields' );
}

/**
 * Set up the venue add/edit screen.
 *
 * @since 1.9.0
 */
function audiotheme_edit_venue_meta_boxes() {
	add_meta_box(
		'venuecontactdiv',
		__( 'Contact <i>(Private)</i>', 'audiotheme' ),
		'audiotheme_venue_contact_meta_box',
		'audiotheme_venue',
		'normal',
		'core'
	);

	add_meta_box(
		'venuenotesdiv',
		__( 'Notes <i>(Private)</i>', 'audiotheme' ),
		'audiotheme_venue_notes_meta_box',
		'audiotheme_venue',
		'normal',
		'core'
	);
}

function audiotheme_venue_edit_assets() {
	wp_enqueue_script( 'audiotheme-venue-edit' );
	wp_enqueue_style( 'jquery-ui-theme-audiotheme' );
}

/**
 *
 */
function audiotheme_venue_details_fields( $post ) {
	$venue = get_audiotheme_venue( $post );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/edit-venue.php' );
}

/**
 * Display venue contact information meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_contact_meta_box( $post ) {
	$venue = get_audiotheme_venue( $post );
	require( AUDIOTHEME_DIR . 'modules/gigs/admin/views/edit-venue-contact.php' );
}

/**
 * Display venue notes meta box.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_notes_meta_box( $post ) {
	$venue = get_audiotheme_venue( $post );
	$notes = format_to_edit( $venue->notes, user_can_richedit() );

	wp_editor( $notes, 'venuenotes', array(
		'editor_css'    => '<style type="text/css" scoped="true">.mceIframeContainer { background-color: #fff;}</style>',
		'media_buttons' => false,
		'textarea_name' => 'audiotheme_venue[notes]',
		'textarea_rows' => 6,
		'teeny'         => true,
	) );
}

/**
 * Process and save venue info when the CPT is saved.
 *
 * @since 1.9.0
 *
 * @param int $venue_id Venue post ID.
 * @param WP_Post $post Venue post object.
 */
function audiotheme_venue_save_post( $post_id, $post ) {
	static $is_active = false; // Prevent recursion.

	$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	$is_revision    = wp_is_post_revision( $post_id );
	$is_valid_nonce = isset( $_POST['audiotheme_venue_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_venue_nonce'], 'save-venue_' . $post_id );

	// Bail if the data shouldn't be saved or intention can't be verified.
	if ( $is_active || $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	$data = array();
	$data['ID'] = absint( $_POST['post_ID'] );
	$data = array_merge( $data, $_POST['audiotheme_venue'] );

	$is_active = true;
	save_audiotheme_venue( $data );
	$is_active = false;
}
