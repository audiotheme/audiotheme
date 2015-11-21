<?php
/**
 * Video post type registration and integration.
 *
 * @package   AudioTheme\Videos
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class for registering the video post type and integration.
 *
 * @package AudioTheme\Videos
 * @since   1.9.0
 */
class AudioTheme_PostType_Video extends AudioTheme_PostType {
	/**
	 * Videos module.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Videos
	 */
	protected $module;

	/**
	 * Post type name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $post_type = 'audiotheme_video';

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Module_Videos $module Gigs module.
	 */
	public function __construct( AudioTheme_Module_Videos $module ) {
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'init',                  array( $this, 'register_post_type' ) );
		add_action( 'pre_get_posts',         array( $this, 'sort_query' ) );
		add_action( 'pre_get_posts',         array( $this, 'default_template_archive_query' ) );
		add_action( 'delete_attachment',     array( $this, 'delete_oembed_thumbnail_data' ) );
		add_filter( 'post_class',            array( $this, 'archive_post_class' ) );
		add_action( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Sort video archive requests.
	 *
	 * Defaults to sorting by publish date in descending order. A plugin can
	 * hook into pre_get_posts at an earlier priority and manually set the order.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object. Passed by reference.
	 */
	public function sort_query( $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() || ! $this->is_archive_request() ) {
			return;
		}

		$orderby = $wp_query->get( 'orderby' );
		if ( $orderby ) {
			return;
		}

		$orderby = get_audiotheme_archive_meta( 'orderby', true, 'post_date', 'audiotheme_video' );
		switch ( $orderby ) {
			// Use a plugin like Simple Page Ordering to change the menu order.
			case 'custom' :
				$wp_query->set( 'orderby', 'menu_order' );
				$wp_query->set( 'order', 'asc' );
				break;

			case 'title' :
				$wp_query->set( 'orderby', 'title' );
				$wp_query->set( 'order', 'asc' );
				break;

			// Sort videos by publish date.
			default :
				$wp_query->set( 'orderby', 'post_date' );
				$wp_query->set( 'order', 'desc' );
		}
	}

	/**
	 * Set posts per page for video archives if the default templates are being
	 * loaded.
	 *
	 * The default video archive template uses a 4-column grid. If it's loaded
	 * from the plugin, set the posts per page arg to a multiple of 4.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object. Passed by reference.
	 */
	public function default_template_archive_query( $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() || ! $this->is_archive_request() ) {
			return;
		}

		// The default video archive template uses a 4-column grid.
		// If it's being loaded from the plugin, set the posts per page arg to a multiple of 4.
		if ( is_audiotheme_default_template( audiotheme_locate_template( 'archive-video.php' ) ) ) {
			if ( '' === $wp_query->get( 'posts_per_archive_page' ) ) {
				$wp_query->set( 'posts_per_archive_page', 12 );
			}
		}
	}

	/**
	 * Delete oEmbed thumbnail post meta if the associated attachment is deleted.
	 *
	 * @since 1.9.0
	 *
	 * @param int $attachment_id The ID of the attachment being deleted.
	 */
	public function delete_oembed_thumbnail_data( $attachment_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT post_id
			FROM $wpdb->postmeta
			WHERE meta_key = '_audiotheme_oembed_thumbnail_id' AND meta_value = %d",
			$attachment_id
		);

		$post_id = $wpdb->get_var( $sql );

		if ( $post_id ) {
			delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id' );
			delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url' );
		}
	}

	/**
	 * Add classes to video posts on the archive page.
	 *
	 * @since 1.9.0
	 *
	 * @param array $classes Default post classes.
	 * @return array
	 */
	public function archive_post_class( $classes ) {
		global $wp_query;

		if ( $wp_query->is_main_query() && is_post_type_archive( 'audiotheme_video' ) ) {
			$nth_child_classes = audiotheme_nth_child_classes( array(
				'current' => $wp_query->current_post + 1,
				'max'     => get_audiotheme_archive_meta( 'columns', true, 4 ),
			) );

			$classes = array_merge( $classes, $nth_child_classes );
		}

		return $classes;
	}

	/**
	 * Retrieve post type registration argments.
	 *
	 * @since 1.9.0
	 *
	 * @return array
	 */
	protected function get_args() {
		return array(
			'has_archive'        => $this->module->get_rewrite_base(),
			'hierarchical'       => true,
			'labels'             => $this->get_labels(),
			'menu_icon'          => audiotheme_encode_svg( 'admin/images/dashicons/videos.svg' ),
			'menu_position'      => 514,
			'public'             => true,
			'publicly_queryable' => true,
			'rewrite'            => array(
				'slug'       => $this->module->get_rewrite_base(),
				'with_front' => false,
			),
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'author' ),
		);
	}

	/**
	 * Retrieve post type labels.
	 *
	 * @since 1.9.0
	 *
	 * @return array
	 */
	protected function get_labels() {
		return array(
			'name'                  => esc_html_x( 'Videos', 'post type general name', 'audiotheme' ),
			'singular_name'         => esc_html_x( 'Video', 'post type singular name', 'audiotheme' ),
			'add_new'               => esc_html_x( 'Add New', 'video', 'audiotheme' ),
			'add_new_item'          => esc_html__( 'Add New Video', 'audiotheme' ),
			'edit_item'             => esc_html__( 'Edit Video', 'audiotheme' ),
			'new_item'              => esc_html__( 'New Video', 'audiotheme' ),
			'view_item'             => esc_html__( 'View Video', 'audiotheme' ),
			'search_items'          => esc_html__( 'Search Videos', 'audiotheme' ),
			'not_found'             => esc_html__( 'No videos found', 'audiotheme' ),
			'not_found_in_trash'    => esc_html__( 'No videos found in Trash', 'audiotheme' ),
			'parent_item_colon'     => esc_html__( 'Parent Video:', 'audiotheme' ),
			'all_items'             => esc_html__( 'All Videos', 'audiotheme' ),
			'menu_name'             => esc_html_x( 'Videos', 'admin menu name', 'audiotheme' ),
			'name_admin_bar'        => esc_html_x( 'Videos', 'add new on admin bar', 'audiotheme' ),
			'archives'              => esc_html__( 'Post Archives', 'audiotheme' ),
			'insert_into_item'      => esc_html__( 'Insert into video', 'audiotheme' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this video', 'audiotheme' ),
			'featured_image'        => esc_html__( 'Featured Image', 'audiotheme' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'audiotheme' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'audiotheme' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'audiotheme' ),
			'filter_items_list'     => esc_html__( 'Filter videos list', 'audiotheme' ),
			'items_list_navigation' => esc_html__( 'Videos list navigation', 'audiotheme' ),
			'items_list'            => esc_html__( 'Videos list', 'audiotheme' ),
		);
	}

	/**
	 * Retrieve post updated messages.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	protected function get_updated_messages( $post ) {
		return array(
			0  => '', // Unused. Messages start at index 1.
			1  => esc_html__( 'Video updated.', 'audiotheme' ),
			2  => esc_html__( 'Custom field updated.', 'audiotheme' ),
			3  => esc_html__( 'Custom field deleted.', 'audiotheme' ),
			4  => esc_html__( 'Video updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( esc_htmlesc_html__( 'Video restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Video published.', 'audiotheme' ),
			7  => esc_html__( 'Video saved.', 'audiotheme' ),
			8  => esc_html__( 'Video submitted.', 'audiotheme' ),
			9  => sprintf(
				esc_html__( 'Video scheduled for: %s.', 'audiotheme' ),
				/* translators: Publish box date format, see http://php.net/date */
				'<strong>' . date_i18n( esc_html__( 'M j, Y @ H:i', 'audiotheme' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => esc_html__( 'Video draft updated.', 'audiotheme' ),
			'preview' => esc_html__( 'Preview video', 'audiotheme' ),
			'view'    => esc_html__( 'View video', 'audiotheme' ),
		);
	}

	/**
	 * Whether the current request is for a video archive.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	protected function is_archive_request() {
		return is_post_type_archive( $this->post_type ) || is_tax( 'audiotheme_video_category' );
	}
}
