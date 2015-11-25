<?php
/**
 * Video category taxonomy registration and integration.
 *
 * @package   AudioTheme\Videos
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Class for registering the video category taxonomy and integration.
 *
 * @package AudioTheme\Videos
 * @since   1.9.0
 */
class AudioTheme_Taxonomy_VideoCategory {
	/**
	 * Module.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Videos
	 */
	protected $module;

	/**
	 * Taxonomy name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $taxonomy = 'audiotheme_video_category';

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Module_Videos $module Videos module.
	 */
	public function __construct( $module ) {
		$this->module = $module;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'init',                  array( $this, 'register_taxonomy' ) );
		add_action( 'pre_get_posts',         array( $this, 'video_category_query' ), 9 );
		add_action( 'term_updated_messages', array( $this, 'term_updated_messages' ) );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 1.9.0
	 */
	public function register_taxonomy() {
		register_taxonomy( 'audiotheme_video_category', 'audiotheme_video', $this->get_args() );
	}

	/**
	 * Set video category requests to use the same archive settings as videos.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Query $wp_query The main WP_Query instance. Passed by reference.
	 */
	public function video_category_query( $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() || ! is_tax( $this->taxonomy ) ) {
			return;
		}

		// @todo Inject the reference to the main plugin class.
		audiotheme()->modules['archives']->set_current_archive_post_type( 'audiotheme_video' );
	}

	/**
	 * Term updated messages.
	 *
	 * @since 1.9.0
	 *
	 * @param array $messages Term update messages.
	 * @return array
	 */
	public function term_updated_messages( $messages ) {
		$messages[ $this->taxonomy ] = array(
			0 => '', // 0 = unused. Messages start at index 1.
			1 => esc_html__( 'Category added.', 'audiotheme' ),
			2 => esc_html__( 'Category deleted.', 'audiotheme' ),
			3 => esc_html__( 'Category updated.', 'audiotheme' ),
			4 => esc_html__( 'Category not added.', 'audiotheme' ),
			5 => esc_html__( 'Category not updated.', 'audiotheme' ),
			6 => esc_html__( 'Categories deleted.', 'audiotheme' ),
		);

		return $messages;
	}

	/**
	 * Retrieve taxonomy registration arguments.
	 *
	 * @since 1.9.0
	 */
	protected function get_args() {
		return array(
			'args'              => array( 'orderby' => 'term_order' ),
			'hierarchical'      => true,
			'labels'            => $this->get_labels(),
			'public'            => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'          => $this->module->get_rewrite_base() . '/' . $this->get_rewrite_base(),
				'with_front'    => false,
			),
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
		);
	}

	/**
	 * Retrieve taxonomy labels.
	 *
	 * @since 1.9.0
	 *
	 * @return array
	 */
	protected function get_labels() {
		return array(
			'name'                       => esc_html_x( 'Categories', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => esc_html_x( 'Category', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => esc_html__( 'Search Categories', 'audiotheme' ),
			'popular_items'              => esc_html__( 'Popular Categories', 'audiotheme' ),
			'all_items'                  => esc_html__( 'All Categories', 'audiotheme' ),
			'parent_item'                => esc_html__( 'Parent Category', 'audiotheme' ),
			'parent_item_colon'          => esc_html__( 'Parent Category:', 'audiotheme' ),
			'edit_item'                  => esc_html__( 'Edit Category', 'audiotheme' ),
			'view_item'                  => esc_html__( 'View Category', 'audiotheme' ),
			'update_item'                => esc_html__( 'Update Category', 'audiotheme' ),
			'add_new_item'               => esc_html__( 'Add New Category', 'audiotheme' ),
			'new_item_name'              => esc_html__( 'New Category Name', 'audiotheme' ),
			'separate_items_with_commas' => esc_html__( 'Separate categories with commas', 'audiotheme' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove categories', 'audiotheme' ),
			'choose_from_most_used'      => esc_html__( 'Choose from most used categories', 'audiotheme' ),
			'menu_name'                  => esc_html_x( 'Categories', 'admin menu name', 'audiotheme' ),
			'not_found'                  => esc_html__( 'No categories found.', 'audiotheme' ),
			'no_terms'                   => esc_html__( 'No categories', 'audiotheme' ),
			'items_list_navigation'      => esc_html__( 'Categories list navigation', 'audiotheme' ),
			'items_list'                 => esc_html__( 'Categories list', 'audiotheme' ),
		);
	}

	/**
	 * Retrieve the base slug to use for rewrite rules.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	protected function get_rewrite_base() {
		$slug = preg_replace( '/[^a-z0-9-_]/', '', _x( 'category', 'video category permalink slug', 'audiotheme' ) );

		if ( empty( $slug ) ) {
			$slug = 'category';
		}

		return apply_filters( 'audiotheme_video_category_rewrite_base', $slug );
	}
}
