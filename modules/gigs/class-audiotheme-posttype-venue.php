<?php
/**
 * Venue post type registration and integration.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */

/**
 * Class for registering the venue post type and integration.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */
class AudioTheme_PostType_Venue extends AudioTheme_PostType {
	/**
	 * Post type name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $post_type = 'audiotheme_venue';

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'init',                  array( $this, 'register_post_type' ) );
		add_action( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Retrieve post type registration argments.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_args() {
		return array(
			'has_archive'        => false,
			'hierarchical'       => false,
			'labels'             => $this->get_labels(),
			'public'             => false,
			'publicly_queryable' => false,
			'query_var'          => 'audiotheme_venue',
			'rewrite'            => false,
			'show_in_menu'       => 'edit.php?post_type=audiotheme_gig',
			'show_ui'            => true,
			'supports'           => array( 'title' ),
		);
	}

	/**
	 * Retrieve post type labels.
	 *
	 * @return array
	 */
	protected function get_labels() {
		return array(
			'name'                  => esc_html_x( 'Venues', 'post type general name', 'audiotheme' ),
			'singular_name'         => esc_html_x( 'Venue', 'post type singular name', 'audiotheme' ),
			'add_new'               => esc_html_x( 'Add New', 'venue', 'audiotheme' ),
			'add_new_item'          => esc_html__( 'Add New Venue', 'audiotheme' ),
			'edit_item'             => esc_html__( 'Edit Venue', 'audiotheme' ),
			'new_item'              => esc_html__( 'New Venue', 'audiotheme' ),
			'view_item'             => esc_html__( 'View Venue', 'audiotheme' ),
			'search_items'          => esc_html__( 'Search Venues', 'audiotheme' ),
			'not_found'             => esc_html__( 'No venues found', 'audiotheme' ),
			'not_found_in_trash'    => esc_html__( 'No venues found in Trash', 'audiotheme' ),
			'parent_item_colon'     => esc_html__( 'Parent Venue:', 'audiotheme' ),
			'all_items'             => esc_html__( 'Venues', 'audiotheme' ),
			'menu_name'             => esc_html_x( 'Venues', 'admin menu name', 'audiotheme' ),
			'name_admin_bar'        => esc_html_x( 'Venues', 'add new on admin bar', 'audiotheme' ),
			'archives'              => esc_html__( 'Post Archives', 'audiotheme' ),
			'insert_into_item'      => esc_html__( 'Insert into venue', 'audiotheme' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this venue', 'audiotheme' ),
			'featured_image'        => esc_html__( 'Featured Image', 'audiotheme' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'audiotheme' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'audiotheme' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'audiotheme' ),
			'filter_items_list'     => esc_html__( 'Filter venues list', 'audiotheme' ),
			'items_list_navigation' => esc_html__( 'Venues list navigation', 'audiotheme' ),
			'items_list'            => esc_html__( 'Venues list', 'audiotheme' ),
		);
	}

	/**
	 * Retrieve post updated messages.
	 *
	 * @since 1.9.0
	 *
	 * @param  WP_Post $post Post object.
	 * @return array
	 */
	protected function get_updated_messages( $post ) {
		return array(
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Venue updated.', 'audiotheme' ),
			2  => esc_html__( 'Custom field updated.', 'audiotheme' ),
			3  => esc_html__( 'Custom field deleted.', 'audiotheme' ),
			4  => esc_html__( 'Venue updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( esc_htmlesc_html__( 'Venue restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Venue published.', 'audiotheme' ),
			7  => esc_html__( 'Venue saved.', 'audiotheme' ),
			8  => esc_html__( 'Venue submitted.', 'audiotheme' ),
			9  => sprintf(
					esc_html__( 'Venue scheduled for: %s.', 'audiotheme' ),
					/* translators: Publish box date format, see http://php.net/date */
					'<strong>' . date_i18n( esc_html__( 'M j, Y @ H:i', 'audiotheme' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => esc_html__( 'Venue draft updated.', 'audiotheme' ),
			'preview' => esc_html__( 'Preview venue', 'audiotheme' ),
			'view'    => esc_html__( 'View venue', 'audiotheme' ),
		);
	}
}
