<?php
/**
 * Videos Init
 *
 * @since 1.0
 */
add_action( 'init', 'audiotheme_videos_init' );

function audiotheme_videos_init() {
	register_post_type( 'audiotheme_video', array(
		'capability_type'        => 'post',
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
			'all_items'          => __( 'Videos', 'audiotheme-i18n' )
		),
		'menu_position'          => 8,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'videos', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
		'taxonomies'             => array( 'post_tag' )
	) );
	
	
	register_taxonomy( 'audiotheme_video_type', 'audiotheme_video', array(
		'args'                           => array( 'orderby' => 'term_order' ),
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
			'choose_from_most_used'      => __( 'Choose from most used video types', 'audiotheme-i18n' )
		),
		'public'                         => true,
		'query_var'                      => true,
		'rewrite'                        => array( 'slug' => 'videos/type', 'with_front' => false ),
		'show_ui'                        => true,
		'show_in_nav_menus'              => true
	) );
}


/**
 * Video Inclusions
 *
 * @since 1.0
 */
require( AUDIOTHEME_DIR . 'videos/general-template.php' );

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'videos/admin/videos.php' );
}
?>