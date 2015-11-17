<?php
/**
 * Record type taxonomy registration and integration.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */

/**
 * Class for registering the record type taxonomy and integration.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */
class AudioTheme_Taxonomy_RecordType {
	/**
	 * Module.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Discography
	 */
	protected $module;

	/**
	 * Taxonomy name.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $taxonomy = 'audiotheme_record_type';

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Module_Discography $module Discography module.
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
		add_action( 'pre_get_posts',         array( $this, 'record_type_query' ), 9 );
		add_action( 'term_updated_messages', array( $this, 'term_updated_messages' ) );
	}

	/**
	 * Register taxonomies.
	 *
	 * @since 1.9.0
	 */
	public function register_taxonomy() {
		register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', $this->get_args() );
	}

	/**
	 * Set record type requests to use the same archive settings as records.
	 *
	 * @since 1.9.0
	 *
	 * @param WP_Query $wp_query The main WP_Query instance. Passed by reference.
	 */
	public function record_type_query( $wp_query ) {
		if ( is_admin() || ! $wp_query->is_main_query() || ! is_tax( $this->taxonomy ) ) {
			return;
		}

		// @todo Inject the reference to the main plugin class.
		audiotheme()->modules['archives']->set_current_archive_post_type( 'audiotheme_record' );
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
			1 => esc_html__( 'Record Type added.', 'audiotheme' ),
			2 => esc_html__( 'Record Type deleted.', 'audiotheme' ),
			3 => esc_html__( 'Record Type updated.', 'audiotheme' ),
			4 => esc_html__( 'Record Type not added.', 'audiotheme' ),
			5 => esc_html__( 'Record Type not updated.', 'audiotheme' ),
			6 => esc_html__( 'Record Types deleted.', 'audiotheme' ),
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
			'meta_box_cb'       => 'audiotheme_taxonomy_checkbox_list_meta_box',
			'public'            => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => $this->module->get_rewrite_base() . '/' . $this->get_rewrite_base(),
				'with_front' => false,
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
			'name'                       => esc_html_x( 'Record Types', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => esc_html_x( 'Record Type', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => esc_html__( 'Search Record Types', 'audiotheme' ),
			'popular_items'              => esc_html__( 'Popular Record Types', 'audiotheme' ),
			'all_items'                  => esc_html__( 'All Record Types', 'audiotheme' ),
			'parent_item'                => esc_html__( 'Parent Record Type', 'audiotheme' ),
			'parent_item_colon'          => esc_html__( 'Parent Record Type:', 'audiotheme' ),
			'edit_item'                  => esc_html__( 'Edit Record Type', 'audiotheme' ),
			'view_item'                  => esc_html__( 'View Record Type', 'audiotheme' ),
			'update_item'                => esc_html__( 'Update Record Type', 'audiotheme' ),
			'add_new_item'               => esc_html__( 'Add New Record Type', 'audiotheme' ),
			'new_item_name'              => esc_html__( 'New Record Type Name', 'audiotheme' ),
			'separate_items_with_commas' => esc_html__( 'Separate record types with commas', 'audiotheme' ),
			'add_or_remove_items'        => esc_html__( 'Add or remove record types', 'audiotheme' ),
			'choose_from_most_used'      => esc_html__( 'Choose from most used record types', 'audiotheme' ),
			'menu_name'                  => esc_html_x( 'Record Types', 'admin menu name', 'audiotheme' ),
			'not_found'                  => esc_html__( 'No record types found.', 'audiotheme' ),
			'no_terms'                   => esc_html__( 'No record types', 'audiotheme' ),
			'items_list_navigation'      => esc_html__( 'Record Types list navigation', 'audiotheme' ),
			'items_list'                 => esc_html__( 'Record Types list', 'audiotheme' ),
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
		$slug = preg_replace( '/[^a-z0-9-_]/', '', _x( 'type', 'record type permalink slug', 'audiotheme' ) );

		if ( empty( $slug ) ) {
			$slug = 'type';
		}

		return apply_filters( 'audiotheme_record_type_rewrite_base', $slug );
	}
}
