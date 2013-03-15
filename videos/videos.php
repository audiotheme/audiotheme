<?php
/**
 * Set up video-related functionality in the AudioTheme framework.
 *
 * @package AudioTheme_Framework
 * @subpackage Videos
 */

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

/**
 * Register video post type and attach hooks to load related functionality.
 *
 * @since 1.0.0
 * @uses register_post_type()
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
	add_action( 'delete_attachment', 'audiotheme_video_delete_attachment' );
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
 * Delete oEmbed thumbnail post meta if the associated attachment is deleted.
 *
 * @since 1.0.0
 *
 * @param int $attachment_id The ID of the attachment being deleted.
 */
function audiotheme_video_delete_attachment( $attachment_id ) {
	global $wpdb;

	$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_audiotheme_oembed_thumbnail_id' AND meta_value=%d", $attachment_id ) );
	if ( $post_id ) {
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id' );
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url' );
	}
}