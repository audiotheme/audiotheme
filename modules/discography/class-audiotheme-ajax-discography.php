<?php
/**
 * Discography AJAX actions.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Discography AJAX actions class.
 *
 * @package AudioTheme\Discography
 * @since   1.9.0
 */
class AudioTheme_AJAX_Discography {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_audiotheme_ajax_get_default_track',    array( $this, 'get_default_track' ) );
		add_action( 'wp_ajax_audiotheme_ajax_get_playlist_track',   array( $this, 'get_playlist_track' ) );
		add_action( 'wp_ajax_audiotheme_ajax_get_playlist_tracks',  array( $this, 'get_playlist_tracks' ) );
		add_action( 'wp_ajax_audiotheme_ajax_get_playlist_records', array( $this, 'get_playlist_records' ) );
	}

	/**
	 * Create a default track for use in the tracklist repeater.
	 *
	 * @since 1.9.0
	 */
	public function get_default_track() {
		$is_valid_nonce = ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'get-default-track_' . $_POST['record'] );

		if ( empty( $_POST['record'] ) || ! $is_valid_nonce ) {
			wp_send_json_error();
		}

		$data['track'] = get_default_post_to_edit( 'audiotheme_track', true );
		$data['nonce'] = wp_create_nonce( 'get-default-track_' . $_POST['record'] );

		wp_send_json( $data );
	}

	/**
	 * Retrieve a track for use in Cue.
	 *
	 * @since 1.9.0
	 */
	public function get_playlist_track() {
		wp_send_json_success( get_cue_playlist_track( absint( $_POST['post_id'] ) ) );
	}

	/**
	 * Retrieve a collection of tracks for use in Cue.
	 *
	 * @since 1.9.0
	 */
	public function get_playlist_tracks() {
		$posts = get_posts( array(
			'post_type'      => 'audiotheme_track',
			'post__in'       => array_map( 'absint', array_filter( (array) $_POST['post__in'] ) ),
			'posts_per_page' => -1,
		) );

		$tracks = array();
		foreach ( $posts as $post ) {
			$tracks[] = $this->get_audiotheme_playlist_track( $post );
		}

		wp_send_json_success( $tracks );
	}

	/**
	 * Retrieve a list of records and their corresponding tracks for use in Cue.
	 *
	 * @since 1.9.0
	 */
	public function get_playlist_records() {
		global $wpdb;

		$data = array();
		$page = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? absint( $_POST['posts_per_page'] ) : 2;

		$records = new WP_Query( array(
			'post_type'      => 'audiotheme_record',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'paged'          => $page,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		if ( $records->have_posts() ) {
			foreach ( $records->posts as $record ) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $record->ID ), array( 120, 120 ) );

				$data[ $record->ID ] = array(
					'id'        => $record->ID,
					'title'     => $record->post_title,
					'artist'    => get_audiotheme_record_artist( $record->ID ),
					'release'   => get_audiotheme_record_release_year( $record->ID ),
					'thumbnail' => $image[0],
					'tracks'    => array(),
				);
			}

			$tracks = $wpdb->get_results(
				"SELECT p.ID, p.post_title, p2.ID AS record_id
				FROM $wpdb->posts p
				INNER JOIN $wpdb->posts p2 ON p.post_parent = p2.ID
				WHERE p.post_type = 'audiotheme_track' AND p.post_status = 'publish'
				ORDER BY p.menu_order ASC"
			);

			if ( $tracks ) {
				foreach ( $tracks as $track ) {
					if ( ! isset( $data[ $track->record_id ] ) ) {
						continue;
					}

					$data[ $track->record_id ]['tracks'][] = array(
						'id'    => $track->ID,
						'title' => $track->post_title,
					);
				}
			}

			// Remove records that don't have any tracks.
			foreach ( $data as $key => $item ) {
				if ( empty( $item['tracks'] ) ) {
					unset( $data[ $key ] );
				}
			}
		}

		$send['maxNumPages'] = $records->max_num_pages;
		$send['records'] = array_values( $data );

		wp_send_json_success( $send );
	}

	/**
	 * Convert a track into the format expected by the Cue plugin.
	 *
	 * @since 1.9.0
	 *
	 * @param int|WP_Post $post Post object or ID.
	 * @return object Track object expected by Cue.
	 */
	protected function get_audiotheme_playlist_track( $post = 0 ) {
		$post = get_post( $post );
		$track = new stdClass;

		$track->id       = $post->ID;
		$track->artist   = get_audiotheme_track_artist( $post->ID );
		$track->audioUrl = get_audiotheme_track_file_url( $post->ID );
		$track->title    = get_the_title( $post->ID );

		if ( $thumbnail_id = get_audiotheme_track_thumbnail_id( $post->ID ) ) {
			$size  = apply_filters( 'cue_artwork_size', array( 300, 300 ) );
			$image = image_downsize( $thumbnail_id, $size );

			$track->artworkUrl = $image[0];
		}

		return $track;
	}
}
