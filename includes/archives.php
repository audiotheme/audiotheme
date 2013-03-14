<?php
/**
 * Post type archives admin functionality.
 *
 * This method allows for archive titles, descriptions, and even post type
 * slugs to be easily changed via a familiar interface. It also allows
 * archives to be easily added to nav menus without using a custom link
 * (they stay updated!).
 *
 * @package AudioTheme_Framework
 * @subpackage Archives
 *
 * @since 1.0.0
 */

/**
 *
 */
function register_audiotheme_archives() {
	$labels = array(
		'name'               => _x( 'Archives', 'post format general name', 'audiotheme-i18n' ),
		'singular_name'      => _x( 'Archive', 'post format singular name', 'audiotheme-i18n' ),
		'add_new'            => _x( 'Add New', 'audiotheme_archive',        'audiotheme-i18n' ),
		'add_new_item'       => __( 'Add New Archive',                      'audiotheme-i18n' ),
		'edit_item'          => __( 'Edit Archive',                         'audiotheme-i18n' ),
		'new_item'           => __( 'New Archive',                          'audiotheme-i18n' ),
		'view_item'          => __( 'View Archive',                         'audiotheme-i18n' ),
		'search_items'       => __( 'Search Archives',                      'audiotheme-i18n' ),
		'not_found'          => __( 'No archives found.',                   'audiotheme-i18n' ),
		'not_found_in_trash' => __( 'No archives found in Trash.',          'audiotheme-i18n' ),
		'all_items'          => __( 'All Archives',                         'audiotheme-i18n' ),
		'menu_name'          => __( 'Archives',                             'audiotheme-i18n' ),
		'name_admin_bar'     => _x( 'Archive', 'add new on admin bar',      'audiotheme-i18n' ),
	);

	$args = array(
		'capability_type'            => array( 'post', 'posts' ),
		'capabilities'               => array(
			'delete_post'            => 'delete_audiotheme_archive',
			// Custom caps prevent unnecessary fields from showing up in post_submit_meta_box().
			'create_posts'           => 'create_audiotheme_archives',
			'delete_posts'           => 'delete_audiotheme_archives',
			'delete_private_posts'   => 'delete_audiotheme_archives',
			'delete_published_posts' => 'delete_audiotheme_archives',
			'delete_others_posts'    => 'delete_audiotheme_archives',
			'publish_posts'          => 'publish_audiotheme_archives',
		),
		'exclude_from_search'        => true,
		'has_archive'                => false,
		'hierarchical'               => false,
		'labels'                     => $labels,
		'map_meta_cap'               => true,
		'public'                     => true,
		'publicly_queryable'         => false,
		'rewrite'                    => 'audiotheme_archive', // Allows slug to be edited. Extra rules wont' be generated.
		'query_var'                  => false,
		'show_ui'                    => false,
		'show_in_admin_bar'          => false,
		'show_in_menu'               => false,
		'show_in_nav_menus'          => true,
		'supports'                   => array( 'title', 'editor' ),
	);

	register_post_type( 'audiotheme_archive', apply_filters( 'audiotheme_archive_register_args', $args ) );
}

/**
 * Get archive post IDs.
 *
 * @since 1.0.0
 *
 * @return array Associative array with post types as keys and post IDs as the values.
 */
function get_audiotheme_archive_ids() {
	return ( $archives = get_option( 'audiotheme_archives' ) ) ? $archives : array();
}

/**
 * Get the archive post ID for a particular post type.
 *
 * @since 1.0.0
 *
 * @param string $post_type_name Post type name
 * @return array
 */
function get_audiotheme_post_type_archive( $post_type ) {
	$archives = get_audiotheme_archive_ids();

	return ( empty( $archives[ $post_type ] ) ) ? null : $archives[ $post_type ];
}

/**
 * Determine if a post ID is for a post type archive post.
 *
 * @since 1.0.0
 *
 * @param int $archive_id Post ID.
 * @return string|bool Post type name if true, otherwise false.
 */
function is_audiotheme_post_type_archive_id( $archive_id ) {
	$archives = get_audiotheme_archive_ids();
	return array_search( $archive_id, $archives );
}

/**
 * Save the active archive IDs.
 *
 * Determines when an archive has become inactive and moves it to a separate
 * option so that if it's activated again in the future, a new post won't be
 * created.
 *
 * Will flush rewrite rules if any changes are detected.
 *
 * @since 1.0.0
 *
 * @param array $ids Associative array of post type slugs as keys and archive post IDs as the values.
 */
function audiotheme_archives_save_active_archives( $ids ) {
	$archives = get_audiotheme_archive_ids();
	$diff = array_diff_key( $archives, $ids );

	if ( count( $ids ) != count( $archives ) || $diff ) {
		$inactive = (array) get_option( 'audiotheme_archives_inactive' );

		// Remove $ids from $inactive.
		$inactive = array_diff_key( array_filter( $inactive ), $ids );

		// Move the diff between the $ids parameter and the $archives option to the $inactive option.
		$inactive = array_merge( $inactive, $diff );

		update_option( 'audiotheme_archives', $ids );
		update_option( 'audiotheme_archives_inactive', $inactive );

		// Update post type rewrite base options.
		foreach ( $ids as $post_type => $id ) {
			audiotheme_archives_update_post_type_rewrite_base( $post_type, $id );
		}

		flush_rewrite_rules();
	}
}

/**
 * Flush the rewrite rules when an archive post slug is changed.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID
 * @param WP_Post $post_after Updated post object.
 * @param WP_Post $post_before Post object before udpate.
 */
function audiotheme_archives_post_updated( $post_id, $post_after, $post_before ) {
	if ( ( $post_type = is_audiotheme_post_type_archive_id( $post_id ) ) && $post_after->post_name != $post_before->post_name ) {
		audiotheme_archives_update_post_type_rewrite_base( $post_type, $post_id );
		flush_rewrite_rules();
	}
}

/**
 * Remove the post type archive reference if it's deleted.
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID.
 */
function audiotheme_archives_deleted_post( $post_id ) {
	if ( 'audiotheme_archive' != get_post_type( $post_id ) ) {
		return;
	}

	$active = get_audiotheme_archives();
	if ( $key = array_search( $active ) ) {
		unset( $active[ $key ] );
		audiotheme_archives_save_active_archives( $active );
	}
}

/**
 * Update a post type's rewrite base option.
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type slug.
 * @param int $archive_id Archive post ID>
 */
function audiotheme_archives_update_post_type_rewrite_base( $post_type, $archive_id ) {
	$archive = get_post( $archive_id );
	update_option( $post_type . '_rewrite_base', $archive->post_name );
}
