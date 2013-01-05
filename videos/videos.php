<?php
/**
 * Set up video-related functionality in the AudioTheme framework.
 *
 * @package AudioTheme_Framework
 * @subpackage Videos
 */

/**
 * Load videos on init.
 */
add_action( 'init', 'audiotheme_videos_init' );

/**
 * Register video post type and taxonomy and attach hooks to load related
 * functionality.
 *
 * @since 1.0.0
 * @uses register_post_type()
 * @uses register_taxonomy()
 */
function audiotheme_videos_init() {
	// Register the video custom post type.
	register_post_type( 'audiotheme_video', array(
		'has_archive'            => true,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Videos', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Video', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'video', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Video', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Video', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Video', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Video', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Videos', 'audiotheme-i18n' ),
			'not_found'          => __( 'No videos found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No videos found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'Videos', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Videos', 'audiotheme-i18n' ),
			'name_admin_bar'     => _x( 'Video', 'add new on admin bar', 'audiotheme-i18n' ),
		),
		'menu_position'          => 514,
		'public'                 => true,
		'publicly_queryable'     => true,
		'register_meta_box_cb'   => 'audiotheme_video_meta_boxes',
		'rewrite'                => array( 'slug' => 'videos', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'show_in_nav_menus'      => false,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
		'taxonomies'             => array( 'post_tag' ),
	) );

	// Register the video type custom taxonomy.
	register_taxonomy( 'audiotheme_video_type', 'audiotheme_video', array(
		'hierarchical'                   => true,
		'labels'                         => array(
			'name'                       => _x( 'Video Types', 'taxonomy general name', 'audiotheme-i18n' ),
			'singular_name'              => _x( 'Video Type', 'taxonomy singular name', 'audiotheme-i18n' ),
			'search_items'               => __( 'Search Video Types', 'audiotheme-i18n' ),
			'popular_items'              => __( 'Popular Video Types', 'audiotheme-i18n' ),
			'all_items'                  => __( 'All Video Types', 'audiotheme-i18n' ),
			'parent_item'                => __( 'Parent Video Type', 'audiotheme-i18n' ),
			'parent_item_colon'          => __( 'Parent Video Type:', 'audiotheme-i18n' ),
			'edit_item'                  => __( 'Edit Video Type', 'audiotheme-i18n' ),
			'view_item'                  => __( 'View Video Type', 'audiotheme-i18n' ),
			'update_item'                => __( 'Update Video Type', 'audiotheme-i18n' ),
			'add_new_item'               => __( 'Add New Video Type', 'audiotheme-i18n' ),
			'new_item_name'              => __( 'New Video Type Name', 'audiotheme-i18n' ),
			'separate_items_with_commas' => __( 'Separate video types with commas', 'audiotheme-i18n' ),
			'add_or_remove_items'        => __( 'Add or remove video types', 'audiotheme-i18n' ),
			'choose_from_most_used'      => __( 'Choose from most used video types', 'audiotheme-i18n' ),
			'menu_name'                  => __( 'Video Types', 'audiotheme-i18n' ),
		),
		'public'                         => true,
		'query_var'                      => true,
		'rewrite'                        => array( 'slug' => 'videos/type', 'with_front' => false ),
		'show_admin_column'              => true,
		'show_in_nav_menus'              => false,
		'show_ui'                        => true,
		'show_tagcloud'                  => false,
	) );
}

/**
 * Load the video template API.
 */
require( AUDIOTHEME_DIR . 'videos/post-template.php' );

/**
 * Load the admin interface elements and functionality for videos.
 */
if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'videos/admin/videos.php' );
}
