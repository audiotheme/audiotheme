<?php
/**
 * Edit Track administration screen integration.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class providing integration with the Edit Track administration screen.
 *
 * @package AudioTheme\Discography
 * @since   1.9.0
 */
class AudioTheme_Screen_EditTrack {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'load-post.php',                   array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',               array( $this, 'load_screen' ) );
		add_action( 'add_meta_boxes_audiotheme_track', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_audiotheme_track',      array( $this, 'on_track_save' ) );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 1.9.0
	 */
	public function load_screen() {
		if ( 'audiotheme_record' !== get_current_screen()->id ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register record meta boxes.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post The record post object being edited.
	 */
	public function register_meta_boxes( $post ) {
		add_meta_box(
			'audiotheme-track-details',
			esc_html__( 'Track Details', 'audiotheme' ),
			array( $this, 'display_details_meta_box' ),
			'audiotheme_track',
			'side',
			'default'
		);
	}

	/**
	 * Enqueue assets for the Edit Record screen.
	 *
	 * @since 1.9.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-media' );
	}

	/**
	 * Display track details meta box.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post The track post object being edited.
	 */
	public function display_details_meta_box( $post ) {
		wp_nonce_field( 'update-track_' . $post->ID, 'audiotheme_track_nonce' );
		require( AUDIOTHEME_DIR . 'modules/discography/admin/views/meta-box-track-details.php' );
	}

	/**
	 * Process and save track info when the CPT is saved.
	 *
	 * @since 1.9.0
	 * @todo Get ID3 info for remote files.
	 *
	 * @param int $post_id Post ID.
	 */
	public function on_track_save( $post_id ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_track_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_track_nonce'], 'update-track_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		$track  = get_post( $post_id );
		$fields = array( 'artist', 'file_url', 'length', 'purchase_url' );

		foreach ( $fields as $field ) {
			$value = empty( $_POST[ $field ] ) ? '' : $_POST[ $field ];

			if ( 'artist' === $field ) {
				$value = sanitize_text_field( $value );
			} elseif ( 'length' === $field ) {
				$value = preg_replace( '/[^0-9:]/', '', $value );
			} elseif ( ( 'file_url' === $field || 'purchase_url' === $field ) && ! empty( $value ) ) {
				$value = esc_url_raw( $value );
			}

			update_post_meta( $post_id, '_audiotheme_' . $field, $value );
		}

		$is_downloadable = empty( $_POST['is_downloadable'] ) ? null : 1;
		update_post_meta( $post_id, '_audiotheme_is_downloadable', $is_downloadable );

		audiotheme_record_update_track_count( $track->post_parent );
	}
}
