<?php
/**
 * Record post type registration and integration.
 *
 * @package   AudioTheme\Discography
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class for registering the record post type and integration.
 *
 * @package AudioTheme\Discography
 * @since   1.9.0
 */
class AudioTheme_PostType_Record extends AudioTheme_PostType_AbstractPostType {
	/**
	 * Discography module.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Discography
	 */
	protected $module;

	/**
	 * Post type name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $post_type = 'audiotheme_record';

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Module_Discography $module Gigs module.
	 */
	public function __construct( AudioTheme_Module_Discography $module ) {
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'init',                   array( $this, 'register_post_type' ) );
		add_action( 'pre_get_posts',          array( $this, 'sort_query' ) );
		add_action( 'pre_get_posts',          array( $this, 'default_template_archive_query' ) );
		add_filter( 'post_type_archive_link', array( $this, 'archive_permalink' ), 10, 2 );
		add_filter( 'post_type_link',         array( $this, 'post_permalink' ), 10, 4 );
		add_filter( 'post_class',             array( $this, 'archive_post_class' ) );
		add_action( 'post_updated_messages',  array( $this, 'post_updated_messages' ) );
	}

	/**
	 * Sort record archive requests.
	 *
	 * Defaults to sorting by release year in descending order. An option is
	 * available on the archive page to sort by title or a custom order. The custom
	 * order using the 'menu_order' value, which can be set using a plugin like
	 * Simple Page Ordering.
	 *
	 * Alternatively, a plugin can hook into pre_get_posts at an earlier priority
	 * and manually set the order.
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

		$orderby = get_audiotheme_archive_meta( 'orderby', true, 'release_year', 'audiotheme_record' );
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

			// Sort records by release year, then by title.
			default :
				$wp_query->set( 'meta_key', '_audiotheme_release_year' );
				$wp_query->set( 'orderby', 'meta_value_num' );
				$wp_query->set( 'order', 'desc' );
				add_filter( 'posts_orderby_request', array( $this, 'sort_query_sql' ) );
		}

		do_action_ref_array( 'audiotheme_record_query_sort', array( &$query ) );
	}

	/**
	 * Sort records by title after sorting by release year.
	 *
	 * @since 1.9.0
	 *
	 * @global wpdb $wpdb
	 *
	 * @param string $orderby SQL order clause.
	 * @return string
	 */
	public function sort_query_sql( $orderby ) {
		global $wpdb;
		return $orderby . ", {$wpdb->posts}.post_title ASC";
	}

	/**
	 * Set posts per page for record archives if the default templates are being
	 * loaded.
	 *
	 * The default record archive template uses a 4-column grid. If it's loaded from
	 * the plugin, set the posts per page arg to a multiple of 4.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Query $wp_query The main WP_Query object. Passed by reference.
	 */
	public function default_template_archive_query( $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() || ! $this->is_archive_request() ) {
			return;
		}

		$template = audiotheme_locate_template( 'archive-record.php' );
		if ( ! is_audiotheme_default_template( $template ) ) {
			return;
		}

		if ( '' === $wp_query->get( 'posts_per_archive_page' ) ) {
			$wp_query->set( 'posts_per_archive_page', 12 );
		}
	}

	/**
	 * Filter the permalink for the discography archive.
	 *
	 * @since 1.9.0
	 *
	 * @param string $link The default archive URL.
	 * @param string $post_type Post type.
	 * @return string The discography archive URL.
	 */
	public function archive_permalink( $link, $post_type ) {
		$permalink = get_option( 'permalink_structure' );
		if ( ! empty( $permalink ) && 'audiotheme_record' === $post_type ) {
			$link = home_url( '/' . $this->module->get_rewrite_base() . '/' );
		}

		return $link;
	}

	/**
	 * Filter record permalinks to match the custom rewrite rules.
	 *
	 * Allows the standard WordPress API function get_permalink() to return the
	 * correct URL when used with a record post type.
	 *
	 * @since 1.9.0
	 *
	 * @see get_post_permalink()
	 *
	 * @param string $post_link The default permalink.
	 * @param object $post The record post object to get the permalink for.
	 * @param bool   $leavename Whether to keep the post name.
	 * @param bool   $sample Is it a sample permalink.
	 * @return string
	 */
	public function post_permalink( $post_link, $post, $leavename, $sample ) {
		if ( $this->is_draft_or_pending( $post ) || 'audiotheme_record' !== get_post_type( $post ) ) {
			return $post_link;
		}

		if ( get_option( 'permalink_structure' ) ) {
			$base      = $this->module->get_rewrite_base();
			$slug      = $leavename ? '%postname%' : $post->post_name;
			$post_link = home_url( sprintf( '/%s/%s/', $base, $slug ) );
		}

		return $post_link;
	}

	/**
	 * Add classes to record posts on the archive page.
	 *
	 * Classes serve as helpful hooks to aid in styling across various browsers.
	 *
	 * - Adds nth-child classes to record posts.
	 *
	 * @since 1.9.0
	 *
	 * @param array $classes Default post classes.
	 * @return array
	 */
	public function archive_post_class( $classes ) {
		global $wp_query;

		if ( $wp_query->is_main_query() && $this->is_archive_request() ) {
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
			'menu_icon'          => audiotheme_encode_svg( 'admin/images/dashicons/discography.svg' ),
			'menu_position'      => 513,
			'public'             => true,
			'publicly_queryable' => true,
			'rewrite'            => false,
			'show_ui'            => true,
			'show_in_admin_bar'  => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		);
	}

	/**
	 * Retrieve post type labels.
	 *
	 * @return array
	 */
	protected function get_labels() {
		return array(
			'name'                  => esc_html_x( 'Records', 'post type general name', 'audiotheme' ),
			'singular_name'         => esc_html_x( 'Record', 'post type singular name', 'audiotheme' ),
			'add_new'               => esc_html_x( 'Add New', 'record', 'audiotheme' ),
			'add_new_item'          => esc_html__( 'Add New Record', 'audiotheme' ),
			'edit_item'             => esc_html__( 'Edit Record', 'audiotheme' ),
			'new_item'              => esc_html__( 'New Record', 'audiotheme' ),
			'view_item'             => esc_html__( 'View Record', 'audiotheme' ),
			'search_items'          => esc_html__( 'Search Records', 'audiotheme' ),
			'not_found'             => esc_html__( 'No records found', 'audiotheme' ),
			'not_found_in_trash'    => esc_html__( 'No records found in Trash', 'audiotheme' ),
			'parent_item_colon'     => esc_html__( 'Parent Record:', 'audiotheme' ),
			'all_items'             => esc_html__( 'All Records', 'audiotheme' ),
			'menu_name'             => esc_html_x( 'Discography', 'admin menu name', 'audiotheme' ),
			'name_admin_bar'        => esc_html_x( 'Records', 'add new on admin bar', 'audiotheme' ),
			'archives'              => esc_html__( 'Post Archives', 'audiotheme' ),
			'insert_into_item'      => esc_html__( 'Insert into record', 'audiotheme' ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this record', 'audiotheme' ),
			'featured_image'        => esc_html__( 'Featured Image', 'audiotheme' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'audiotheme' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'audiotheme' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'audiotheme' ),
			'filter_items_list'     => esc_html__( 'Filter records list', 'audiotheme' ),
			'items_list_navigation' => esc_html__( 'Records list navigation', 'audiotheme' ),
			'items_list'            => esc_html__( 'Records list', 'audiotheme' ),
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
			1  => esc_html__( 'Record updated.', 'audiotheme' ),
			2  => esc_html__( 'Custom field updated.', 'audiotheme' ),
			3  => esc_html__( 'Custom field deleted.', 'audiotheme' ),
			4  => esc_html__( 'Record updated.', 'audiotheme' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( esc_htmlesc_html__( 'Record restored to revision from %s.' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => esc_html__( 'Record published.', 'audiotheme' ),
			7  => esc_html__( 'Record saved.', 'audiotheme' ),
			8  => esc_html__( 'Record submitted.', 'audiotheme' ),
			9  => sprintf(
				esc_html__( 'Record scheduled for: %s.', 'audiotheme' ),
				/* translators: Publish box date format, see http://php.net/date */
				'<strong>' . date_i18n( esc_html__( 'M j, Y @ H:i', 'audiotheme' ), strtotime( $post->post_date ) ) . '</strong>'
			),
			10 => esc_html__( 'Record draft updated.', 'audiotheme' ),
			'preview' => esc_html__( 'Preview record', 'audiotheme' ),
			'view'    => esc_html__( 'View record', 'audiotheme' ),
		);
	}

	/**
	 * Whether the current request is for a record archive.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	protected function is_archive_request() {
		return is_post_type_archive( $this->post_type ) || is_tax( 'audiotheme_record_type' );
	}
}
