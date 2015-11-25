<?php
/**
 * Edit Venue administration screen integration.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class providing integration with the Edit Venue administration screen.
 *
 * @package AudioTheme\Gigs
 * @since   1.9.0
 */
class AudioTheme_Screen_EditVenue extends AudioTheme_Screen_AbstractScreen{
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'load-post.php',                   array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',               array( $this, 'load_screen' ) );
		add_action( 'add_meta_boxes_audiotheme_venue', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_audiotheme_venue',      array( $this, 'on_venue_save' ), 10, 2 );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 1.9.0
	 */
	public function load_screen() {
		if ( 'audiotheme_venue' !== get_current_screen()->id ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'edit_form_after_title', array( $this, 'display_edit_fields' ) );
	}

	/**
	 * Register venue meta boxes.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post The venue post object being edited.
	 */
	public function register_meta_boxes( $post ) {
		add_meta_box(
			'audiotheme-venue-contact',
			esc_html_x( 'Contact', 'venue meta box title', 'audiotheme' ),
			array( $this, 'display_contact_meta_box' ),
			'audiotheme_venue',
			'normal',
			'core'
		);

		add_meta_box(
			'audiotheme-venue-notes',
			esc_html_x( 'Notes', 'venue meta box title', 'audiotheme' ),
			array( $this, 'display_notes_meta_box' ),
			'audiotheme_venue',
			'normal',
			'core'
		);
	}

	/**
	 * Enqueue assets for the Edit Venue screen.
	 *
	 * @since 1.9.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-venue-edit' );
	}

	/**
	 * Set up and display the main venue fields for editing.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_edit_fields( $post ) {
		$venue = get_audiotheme_venue( $post );
		require( $this->plugin->get_path( 'admin/views/edit-venue.php' ) );
	}

	/**
	 * Display venue contact information meta box.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Venue post object.
	 */
	public function display_contact_meta_box( $post ) {
		$venue = get_audiotheme_venue( $post );
		require( $this->plugin->get_path( 'admin/views/edit-venue-contact.php' ) );
	}

	/**
	 * Display venue notes meta box.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Venue post object.
	 */
	public function display_notes_meta_box( $post ) {
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
	 * @param int     $post_id Venue post ID.
	 * @param WP_Post $post Venue post object.
	 */
	public function on_venue_save( $post_id, $post ) {
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
}
