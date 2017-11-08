<?php
/**
 * Edit Venue administration screen integration.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Class providing integration with the Edit Venue administration screen.
 *
 * @package AudioTheme\Gigs
 * @since   2.0.0
 */
class AudioTheme_Screen_EditVenue extends AudioTheme_Screen_AbstractScreen{
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
	 */
	public function load_screen() {
		if ( 'audiotheme_venue' !== get_current_screen()->id ) {
			return;
		}

		add_action( 'edit_form_after_title', array( $this, 'display_edit_fields' ) );
	}

	/**
	 * Register venue meta boxes.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The venue post object being edited.
	 */
	public function register_meta_boxes( $post ) {
		add_meta_box(
			'audiotheme-venue-contact',
			esc_html_x( 'Additional Notes', 'venue meta box title', 'audiotheme' ),
			array( $this, 'display_notes_meta_box' ),
			'audiotheme_venue',
			'normal',
			'core'
		);

		add_meta_box(
			'venue-coordinates',
			esc_html__( 'Venue Coordinates', 'audiotheme' ),
			array( $this, 'display_coordinates_meta_box' ),
			'audiotheme_venue',
			'side',
			'default'
		);
	}

	/**
	 * Set up and display the main venue fields for editing.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_edit_fields( $post ) {
		$venue = get_audiotheme_venue( $post );
		require( $this->plugin->get_path( 'admin/views/edit-venue.php' ) );
	}

	/**
	 * Display venue contact information and notes meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Venue post object.
	 */
	public function display_notes_meta_box( $post ) {
		$venue = get_audiotheme_venue( $post );
		$notes = format_to_edit( $venue->notes, user_can_richedit() );
		require( $this->plugin->get_path( 'admin/views/edit-venue-notes.php' ) );
	}

	/**
	 * Display a meta box for adding coordinates to a venue.
	 *
	 * @since 2.2.0
	 *
	 * @param WP_Post $post Venue post object.
	 */
	public function display_coordinates_meta_box( $post ) {
		?>
		<p class="audiotheme-field">
			<label for="venue-latitude"><?php esc_html_e( 'Latitude', 'audiotheme' ); ?></label>
			<input type="text" name="audiotheme_venue[latitude]" id="venue-latitude" value="<?php echo esc_attr( $post->_audiotheme_latitude ) ; ?>" class="widefat">
		</p>
		<p class="audiotheme-field">
			<label for="venue-longitude"><?php esc_html_e( 'Longitude', 'audiotheme' ); ?></label>
			<input type="text" name="audiotheme_venue[longitude]" id="venue-longitude" value="<?php echo esc_attr( $post->_audiotheme_longitude ) ; ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Process and save venue info when the CPT is saved.
	 *
	 * @since 2.0.0
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
		update_post_meta( $post_id, '_audiotheme_latitude', sanitize_text_field( $_POST['audiotheme_venue']['latitude'] ) );
		update_post_meta( $post_id, '_audiotheme_longitude', sanitize_text_field( $_POST['audiotheme_venue']['longitude'] ) );
		$is_active = false;
	}
}
