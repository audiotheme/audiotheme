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
			'all_items'          => __( 'All Videos', 'audiotheme-i18n' ),
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
	
	add_action( 'template_include', 'audiotheme_video_template_include' );
}

/**
 * Load video templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_video_template_include( $template ) {
	if ( is_post_type_archive( 'audiotheme_video' ) ) {
		$template = locate_template( 'audiotheme/archive-video.php' );
	} elseif ( is_singular( 'audiotheme_video' ) ) {
		$template = locate_template( 'audiotheme/single-video.php' );
	}
	
	return $template;
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
