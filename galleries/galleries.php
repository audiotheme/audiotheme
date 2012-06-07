<?php
/**
 * Galleries Init
 *
 * @since 1.0
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
		'menu_position'          => 9,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'gallery', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' )
	) );
}

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'galleries/admin/galleries.php' );
}
?>