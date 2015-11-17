<?php
/**
 * Manage Venues administration screen integration.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */

/**
 * Class providing integration with the Manage Venues administration screen.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */
class AudioTheme_Screen_ManageVenues {
	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_filter( 'parse_query',                                   array( $this, 'parse_admin_query' ) );
		add_filter( 'manage_audiotheme_venue_posts_columns',         array( $this, 'register_columns' ) );
		add_action( 'manage_posts_custom_column',                    array( $this, 'display_columns' ), 10, 2 );
		add_action( 'manage_edit-audiotheme_venue_sortable_columns', array( $this, 'register_sortable_columns' ) );
	}

	/**
	 * Sort posts on the Manage Venues screen.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object passed by reference.
	 */
	public function parse_admin_query( $wp_query ) {
		// Ensure this only affects requests in the admin panel.
		if (
			! is_admin() ||
			empty( $_GET['post_type'] ) ||
			'audiotheme_venue' !== $_GET['post_type']
		) {
			return;
		}

		$orderby = empty( $_REQUEST['orderby'] ) ? 'title' : sanitize_key( $_REQUEST['orderby'] );
		$order   = isset( $_REQUEST['order'] ) && 'desc' === strtolower( $_REQUEST['order'] ) ? 'desc' : 'asc';
		$wp_query->set( 'order', $order );

		switch ( $orderby ) {
			case 'title':
				$wp_query->set( 'orderby', 'title' );
				break;
			case 'gigs':
				$wp_query->set( 'meta_key', '_audiotheme_gig_count' );
				$wp_query->set( 'orderby', 'meta_value_num' );
				break;
			case 'city':
			case 'country':
			case 'phone':
			case 'state':
				$wp_query->set( 'meta_key', '_audiotheme_' . $orderby );
				$wp_query->set( 'orderby', 'meta_value' );
				break;
		}
	}

	/**
	 * Remove 'Edit' from the bulk edit options.
	 *
	 * @since 1.9.0
	 *
	 * @param array $actions List of actions.
	 * @return array
	 */
	public function bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	/**
	 * Register list table columns.
	 *
	 * @since 1.9.0
	 *
	 * @param array $columns An array of the column names to display.
	 * @return array Filtered array of column names.
	 */
	public function register_columns( $columns ) {
		$columns['title']     = esc_html_x( 'Name', 'column name', 'audiotheme' );
		$columns['city']      = esc_html__( 'City', 'audiotheme' );
		$columns['state']     = esc_html__( 'State', 'audiotheme' );
		$columns['country']   = esc_html__( 'Country', 'audiotheme' );
		$columns['phone']     = esc_html__( 'Phone', 'audiotheme' );
		$columns['gig_count'] = esc_html__( 'Gigs', 'audiotheme' );

		$columns['website']   = sprintf(
			'<span class="audiotheme-column-header-icon dashicons dashicons-admin-links"></span><span class="audiotheme-column-header-label">%s</span>',
			esc_html__( 'Website', 'audiotheme' )
		);

		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Register sortable list table columns.
	 *
	 * @since 1.9.0
	 *
	 * @param array $columns Column query vars with their corresponding column id as the key.
	 * @return array
	 */
	public function register_sortable_columns( $columns ) {
		$columns['title']     = array( 'title', true );
		$columns['city']      = 'city';
		$columns['state']     = 'state';
		$columns['country']   = 'country';
		$columns['phone']     = 'phone';
		$columns['gig_count'] = 'gig_count';

		return $columns;
	}

	/**
	 * Display custom list table columns.
	 *
	 * @since 1.9.0
	 *
	 * @param string $column_id The id of the column to display.
	 * @param int    $post_id Post ID.
	 */
	public function display_columns( $column_id, $post_id ) {
		if ( 'edit-audiotheme_venue' !== get_current_screen()->id ) {
			return;
		}

		$post = get_post( $post_id );

		switch ( $column_id ) {
			case 'gig_count' :
				echo $this->get_gig_count_column_value( $post );
				break;
			case 'website' :
				echo $this->get_website_column_value( $post );
				break;
			default :
				$key = sprintf( '_audiotheme_%s', $column_id );
				echo esc_html( get_post_meta( $post->ID, $key, true ) );
		}
	}

	/**
	 * Filter post row actions.
	 *
	 * @since 1.9.0
	 *
	 * @param array   $actions Post actions.
	 * @param WP_Post $post    Post object.
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {
		if ( 'audiotheme_venue' === get_post_type( $post ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Retrieve the value for the gig count column.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Venue post object.
	 * @return string
	 */
	protected function get_gig_count_column_value( $post ) {
		$count = absint( get_post_meta( $post->ID, '_audiotheme_gig_count', true ) );

		// @todo Update post status?
		$admin_url = add_query_arg( array(
			'post_type'   => 'audiotheme_gig',
			'post_status' => 'any',
			'venue'       => $post->ID,
		), admin_url( 'edit.php' ) );

		$admin_link = sprintf(
			'<a href="%s">%d</a>',
			esc_url( $admin_url ),
			$count
		);

		return empty( $count ) ? $count : $admin_link;
	}

	/**
	 * Retrieve the value for the URL column.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Venue post object.
	 * @return string
	 */
	protected function get_website_column_value( $post ) {
		$url = get_post_meta( $post->ID, '_audiotheme_website', true );

		if ( ! empty( $url ) ) {
			$url = sprintf(
				'<a href="%s" class="venue-website-link" target="_blank"><span class="dashicons dashicons-admin-links"></span><span class="screen-reader-text">%s</span></a>',
				esc_url( $url ),
				esc_attr__( 'Visit venue website', 'audiotheme' )
			);
		}

		return $url;
	}
}
