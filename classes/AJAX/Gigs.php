<?php
/**
 * Gigs AJAX actions.
 *
 * @package   AudioTheme\Gigs
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Gigs AJAX actions class.
 *
 * @package AudioTheme\Gigs
 * @since   2.0.0
 */
class AudioTheme_AJAX_Gigs extends AudioTheme_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_audiotheme_ajax_get_venue_matches', array( $this, 'get_venue_matches' ) );
		add_action( 'wp_ajax_audiotheme_ajax_is_new_venue',      array( $this, 'is_new_venue' ) );
		add_action( 'wp_ajax_audiotheme_ajax_get_venue',         array( $this, 'get_venue' ) );
		add_action( 'wp_ajax_audiotheme_ajax_get_venues',        array( $this, 'get_venues' ) );
		add_action( 'wp_ajax_audiotheme_ajax_save_venue',        array( $this, 'save_venue' ) );
	}

	/**
	 * Search for venues that begin with a string.
	 *
	 * @since 2.0.0
	 */
	public function get_venue_matches() {
		global $wpdb;

		$var    = $wpdb->esc_like( stripslashes( $_GET['term'] ) ) . '%';
		$sql    = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type = 'audiotheme_venue' AND post_title LIKE %s ORDER BY post_title ASC", $var );
		$venues = $wpdb->get_col( $sql );

		wp_send_json( $venues );
	}

	/**
	 * Check for an existing venue with the same name.
	 *
	 * @since 2.0.0
	 */
	public function is_new_venue() {
		global $wpdb;

		$sql   = $wpdb->prepare( "SELECT post_title FROM $wpdb->posts WHERE post_type = 'audiotheme_venue' AND post_title = %s ORDER BY post_title ASC LIMIT 1", stripslashes( $_GET['name'] ) );
		$venue = $wpdb->get_col( $sql );

		wp_send_json( $venue );
	}

	/**
	 * Retrieve a venue.
	 *
	 * @since 2.0.0
	 */
	public function get_venue() {
		$venue = prepare_audiotheme_venue_for_js( absint( $_POST['ID'] ) );
		wp_send_json_success( $venue );
	}

	/**
	 * Retrieve venues.
	 *
	 * @since 2.0.0
	 */
	public function get_venues() {
		$response = array();

		$query_args = isset( $_REQUEST['query_args'] ) ? (array) $_REQUEST['query_args'] : array();
		$query_args = array_intersect_key( $query_args, array_flip( array( 'paged', 'posts_per_page', 's' ) ) );
		$query_args = wp_parse_args( $query_args, array(
			'post_type'      => 'audiotheme_venue',
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );

		$query = new WP_Query( $query_args );

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$response[] = prepare_audiotheme_venue_for_js( $post->ID );
			}
		}

		wp_send_json_success( $response );
	}

	/**
	 * Create or update a venue.
	 *
	 * @since 2.0.0
	 */
	public function save_venue() {
		$data = $_POST['model'];

		if ( empty( $data['ID'] ) ) {
			check_ajax_referer( 'insert-venue', 'nonce' );
		} else {
			check_ajax_referer( 'update-post_' . $data['ID'], 'nonce' );
		}

		if ( empty( $data['post_status'] ) ) {
			$data['post_status'] = 'publish';
		}

		$venue_id = save_audiotheme_venue( $data );
		wp_send_json_success( prepare_audiotheme_venue_for_js( $venue_id ) );
	}
}
