<?php
/**
 * Galleries Init
 *
 * @since 1.0.0
 */
add_action( 'init', 'audiotheme_galleries_init' );

function audiotheme_galleries_init() {
	register_post_type( 'audiotheme_gallery', array(
		'capability_type'        => 'post',
		'has_archive'            => true,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Galleries', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Gallery', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'gallery', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Gallery', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Gallery', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Gallery', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Gallery', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Galleries', 'audiotheme-i18n' ),
			'not_found'          => __( 'No galleries found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No galleries found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Galleries', 'audiotheme-i18n' )
		),
		'menu_position'          => 515,
		'public'                 => true,
		'publicly_queryable'     => true,
		'register_meta_box_cb'   => '', // 'audiotheme_edit_gallery_meta_boxes',
		'rewrite'                => array( 'slug' => 'gallery', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'show_in_nav_menus'      => false,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' )
	) );
}

/**
 * Gallery Includes
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'galleries/post-template.php' );

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'galleries/admin/galleries.php' );
}
?>