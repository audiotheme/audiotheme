<?php
/**
 * Gigs Init
 *
 * @since 1.0
 */
add_action( 'init', 'audiotheme_gigs_init' );
function audiotheme_gigs_init() {
	
	register_post_type( 'audiotheme_gig', array(
		'has_archive'            => 'tour',
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Gigs', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Gig', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'gig', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Gig', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Gig', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Gig', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Gig', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Gigs', 'audiotheme-i18n' ),
			'not_found'          => __( 'No gigs found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No gigs found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Gigs', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Gigs', 'audiotheme-i18n' )
		),
		'menu_position'          => 6,
		'public'                 => true,
		'register_meta_box_cb'   => 'audiotheme_edit_gig_meta_boxes',
		'rewrite'                => false, //array( 'slug' => 'tour/%year%', 'with_front' => false ),
		'show_in_menu'           => 'gigs',
		'supports'               => array( 'title', 'editor', 'thumbnail', '' )
	) );
	
	register_post_type( 'audiotheme_venue', array(
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Venues', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Venue', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'venue', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Venue', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Venue', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Venue', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Venue', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Venues', 'audiotheme-i18n' ),
			'not_found'          => __( 'No venues found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No venues found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Venues', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Venues', 'audiotheme-i18n' )
		),
		'public'                 => false,
		'publicly_queryable'     => false,
		'query_var'              => 'audiotheme_venue',
		'rewrite'                => false,
		'supports'               => array( '' )
	) );
	
}


/**
 * Gig Inclusions
 *
 * @since 1.0
 */
include AUDIOTHEME_DIR . 'gigs/general-template.php';

if ( is_admin() ) {
	include AUDIOTHEME_DIR . 'gigs/admin/gigs.php';
	include AUDIOTHEME_DIR . 'gigs/admin/venues.php';
}
?>