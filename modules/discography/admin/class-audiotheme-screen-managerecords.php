<?php
/**
 * Manage Records administration screen integration.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */

/**
 * Class providing integration with the Manage Records administration screen.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */
class AudioTheme_Screen_ManageRecords {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_filter( 'parse_query',                                    array( $this, 'admin_query' ) );
		add_filter( 'manage_edit-audiotheme_record_columns',          array( $this, 'register_columns' ) );
		add_action( 'manage_edit-audiotheme_record_sortable_columns', array( $this, 'register_sortable_columns' ) );
		add_action( 'manage_pages_custom_column',                     array( $this, 'display_columns' ), 10, 2 );
	}

	/**
	 * Custom sort records on the Manage Records screen.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object. Passed by reference.
	 */
	public function admin_query( $wp_query ) {
		if ( ! is_admin() || ! isset( $_GET['post_type'] ) || 'audiotheme_record' !== $_GET['post_type'] ) {
			return;
		}

		$sortable_keys = array( 'artist', 'release_year', 'tracks' );
		if ( empty( $_GET['orderby'] ) || ! in_array( $_GET['orderby'], $sortable_keys ) ) {
			return;
		}

		switch ( $_GET['orderby'] ) {
			case 'release_year' :
				$meta_key = '_audiotheme_release_year';
				$orderby = 'meta_value_num';
				break;
			case 'tracks' :
				$meta_key = '_audiotheme_track_count';
				$orderby = 'meta_value_num';
				break;
		}

		$order  = isset( $_GET['order'] ) && 'desc' === $_GET['order'] ? 'desc' : 'asc';
		$orderby = empty( $orderby ) ? 'meta_value' : $orderby;

		$wp_query->set( 'meta_key', $meta_key );
		$wp_query->set( 'orderby', $orderby );
		$wp_query->set( 'order', $order );
	}

	/**
	 * Register record columns.
	 *
	 * @since 1.9.0
	 *
	 * @param array $columns An array of the column names to display.
	 * @return array Filtered array of column names.
	 */
	public function register_columns( $columns ) {
		$columns['title'] = esc_html_x( 'Record', 'column_name', 'audiotheme' );

		// Create columns and insert them in the appropriate position in the columns array.
		$image_column = array( 'audiotheme_image' => esc_html_x( 'Image', 'column name', 'audiotheme' ) );
		$columns = audiotheme_array_insert_after_key( $columns, 'cb', $image_column );

		$release_column = array( 'release_year' => esc_html_x( 'Released', 'column_name', 'audiotheme' ) );
		$columns = audiotheme_array_insert_after_key( $columns, 'title', $release_column );

		$columns['track_count'] = esc_html_x( 'Tracks', 'column name', 'audiotheme' );

		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Register sortable record columns.
	 *
	 * @since 1.9.0
	 *
	 * @param array $columns Column query vars with their corresponding column id as the key.
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['release_year'] = 'release_year';
		$columns['track_count']  = 'tracks';
		return $columns;
	}

	/**
	 * Display custom record columns.
	 *
	 * @since 1.9.0
	 *
	 * @param string $column_name The id of the column to display.
	 * @param int    $post_id Post ID.
	 */
	public function display_columns( $column_name, $post_id ) {
		global $post;

		switch ( $column_name ) {
			case 'release_year' :
				echo esc_html( get_audiotheme_record_release_year( $post_id ) );
				break;

			case 'track_count' :
				$args = array(
					'post_type'   => 'audiotheme_track',
					'post_parent' => $post_id,
				);

				printf(
					'<a href="%1$s">%2$s</a>',
					esc_url( add_query_arg( $args, admin_url( 'edit.php' ) ) ),
					absint( get_post_meta( $post_id, '_audiotheme_track_count', true ) )
				);
				break;
		}
	}
}
