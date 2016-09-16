<?php
/**
 * Edit Record administration screen integration.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Class providing integration with the Edit Record administration screen.
 *
 * @package AudioTheme\Discography
 * @since   2.0.0
 */
class AudioTheme_Screen_EditRecord extends AudioTheme_Screen_AbstractScreen{
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'load-post.php',                      array( $this, 'load_screen' ) );
		add_action( 'load-post-new.php',                  array( $this, 'load_screen' ) );
		add_action( 'add_meta_boxes_audiotheme_record',   array( $this, 'register_meta_boxes' ) );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_released_field' ) );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_artist_field' ), 20 );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_genre_field' ), 30 );
		add_action( 'audiotheme_record_details_meta_box', array( $this, 'display_links_field' ), 40 );
		add_action( 'admin_enqueue_scripts',              array( $this, 'register_assets' ), 1 );
		add_action( 'save_post_audiotheme_record',        array( $this, 'on_record_save' ), 10, 2 );
	}

	/**
	 * Set up the screen.
	 *
	 * @since 2.0.0
	 */
	public function load_screen() {
		if ( 'audiotheme_record' !== get_current_screen()->id ) {
			return;
		}

		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_assets' ) );
		add_action( 'edit_form_after_editor', array( $this, 'display_tracklist_editor' ) );
	}

	/**
	 * Register record meta boxes.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The record post object being edited.
	 */
	public function register_meta_boxes( $post ) {
		add_meta_box(
			'audiotheme-record-details',
			esc_html__( 'Record Details', 'audiotheme' ),
			array( $this, 'display_details_meta_box' ),
			'audiotheme_record',
			'side',
			'default'
		);
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function register_assets() {
		wp_register_script(
			'audiotheme-record-edit',
			$this->plugin->get_url( 'admin/js/record-edit.js' ),
			array( 'audiotheme-admin', 'audiotheme-media', 'wp-backbone', 'wp-util' ),
			'2.0.0',
			true
		);
	}

	/**
	 * Enqueue assets for the Edit Record screen.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-record-edit' );
	}

	/**
	 * Tracklist editor.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_tracklist_editor( $post ) {
		$tracks = get_audiotheme_record_tracks( $post->ID );

		if ( $tracks ) {
			foreach ( $tracks as $key => $track ) {
				$tracks[ $key ] = array(
					'key'            => $key,
					'id'             => $track->ID,
					'title'          => esc_attr( $track->post_title ),
					'artist'         => esc_attr( get_post_meta( $track->ID, '_audiotheme_artist', true ) ),
					'fileUrl'        => esc_attr( get_post_meta( $track->ID, '_audiotheme_file_url', true ) ),
					'length'         => esc_attr( get_post_meta( $track->ID, '_audiotheme_length', true ) ),
					'isDownloadable' => is_audiotheme_track_downloadable( $track->ID ),
					'purchaseUrl'    => esc_url( get_post_meta( $track->ID, '_audiotheme_purchase_url', true ) ),
				);
			}
		}

		wp_localize_script( 'audiotheme-record-edit', '_audiothemeTracklistSettings', array(
			'postId' => $post->ID,
			'tracks' => empty( $tracks ) ? null : wp_json_encode( $tracks ),
			'nonce'  => wp_create_nonce( 'get-default-track_' . $post->ID ),
		) );

		require( $this->plugin->get_path( 'admin/views/edit-record-tracklist.php' ) );
		require( $this->plugin->get_path( 'admin/views/templates-record.php' ) );
	}

	/**
	 * Display the record details meta box.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post The record post object being edited.
	 */
	public function display_details_meta_box( $post ) {
		wp_nonce_field( 'update-record_' . $post->ID, 'audiotheme_record_nonce' );
		do_action( 'audiotheme_record_details_meta_box', $post );
	}

	/**
	 * Display a field to edit the record release year.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_released_field( $post ) {
		?>
		<p class="audiotheme-field">
			<label for="record-year"><?php esc_html_e( 'Release Year', 'audiotheme' ); ?></label>
			<input type="text" name="release_year" id="record-year" value="<?php echo esc_attr( get_audiotheme_record_release_year( $post->ID ) ); ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Display a field to edit the record artist.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_artist_field( $post ) {
		?>
		<p class="audiotheme-field">
			<label for="record-artist"><?php esc_html_e( 'Artist', 'audiotheme' ); ?></label>
			<input type="text" name="artist" id="record-artist" value="<?php echo esc_attr( get_audiotheme_record_artist( $post->ID ) ); ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Display a field to edit the record genre.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_genre_field( $post ) {
		?>
		<p class="audiotheme-field">
			<label for="record-genre"><?php esc_html_e( 'Genre', 'audiotheme' ); ?></label>
			<input type="text" name="genre" id="record-genre" value="<?php echo esc_attr( get_audiotheme_record_genre( $post->ID ) ); ?>" class="widefat">
		</p>
		<?php
	}

	/**
	 * Display a field to edit record links.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 */
	public function display_links_field( $post ) {
		$record_links = (array) get_audiotheme_record_links( $post->ID );
		$record_links = empty( $record_links ) ? array( '' ) : $record_links;

		$record_link_sources = get_audiotheme_record_link_sources();
		$record_link_source_names = array_keys( $record_link_sources );
		sort( $record_link_source_names );

		require( $this->plugin->get_path( 'admin/views/edit-record-links.php' ) );
	}

	/**
	 * Process and save record info when the CPT is saved.
	 *
	 * Creates and updates child tracks and saves additional record meta.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post ID.
	 */
	public function on_record_save( $post_id ) {
		$is_autosave    = defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = isset( $_POST['audiotheme_record_nonce'] ) && wp_verify_nonce( $_POST['audiotheme_record_nonce'], 'update-record_' . $post_id );

		// Bail if the data shouldn't be saved or intention can't be verified.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		$current_user = wp_get_current_user();

		// Whitelisted fields.
		$fields = array( 'release_year', 'artist', 'genre' );
		foreach ( $fields as $field ) {
			$value = empty( $_POST[ $field ] ) ? '' : sanitize_text_field( $_POST[ $field ] );
			update_post_meta( $post_id, '_audiotheme_' . $field, $value );
		}

		// Update purchase urls.
		$record_links = array();
		if ( isset( $_POST['record_links'] ) && is_array( $_POST['record_links'] ) ) {
			foreach ( $_POST['record_links'] as $link ) {
				if ( ! empty( $link['name'] ) && ! empty( $link['url'] ) ) {
					$link['url'] = esc_url_raw( $link['url'] );
					$record_links[] = $link;
				}
			}
		}
		update_post_meta( $post_id, '_audiotheme_record_links', $record_links );

		if ( empty( $_POST['audiotheme_tracks'] ) ) {
			return;
		}

		// Update tracklist.
		$i = 1;
		foreach ( $_POST['audiotheme_tracks'] as $data ) {
			$post     = array();
			$data     = wp_parse_args( $data, array( 'artist' => '', 'post_id' => '', 'title' => '' ) );
			$track_id = empty( $data['post_id'] ) ? '' : absint( $data['post_id'] );

			if ( ! empty( $data['title'] ) ) {
				$post['post_title']  = $data['title'];
				$post['post_status'] = 'publish';
				$post['post_parent'] = $post_id;
				$post['menu_order']  = $i;
				$post['post_type']   = 'audiotheme_track';

				// Insert or update track.
				if ( empty( $track_id ) ) {
					$track_id = wp_insert_post( $post );
				} else {
					$post['ID'] = $track_id;
					$post['post_author'] = $current_user->ID;
					wp_update_post( $post );
				}

				$i++;
			}

			// Update track artist and file url.
			if ( ! empty( $track_id ) && ! is_wp_error( $track_id ) ) {
				update_post_meta( $track_id, '_audiotheme_artist', sanitize_text_field( $data['artist'] ) );
				update_post_meta( $track_id, '_audiotheme_file_url', esc_url_raw( $data['file_url'] ) );
				update_post_meta( $track_id, '_audiotheme_length', preg_replace( '/[^0-9:]/', '', $data['length'] ) );
			}
		}

		// Update track count.
		audiotheme_record_update_track_count( $post_id );
	}
}
