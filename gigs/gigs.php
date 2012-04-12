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
			'name'               => _x( 'Gigs', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Gig', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'gig', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Gig', 'audiotheme' ),
			'edit_item'          => __( 'Edit Gig', 'audiotheme' ),
			'new_item'           => __( 'New Gig', 'audiotheme' ),
			'view_item'          => __( 'View Gig', 'audiotheme' ),
			'search_items'       => __( 'Search Gigs', 'audiotheme' ),
			'not_found'          => __( 'No gigs found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No gigs found in Trash', 'audiotheme' ),
			'all_items'          => __( 'All Gigs', 'audiotheme' ),
			'menu_name'          => __( 'Gigs', 'audiotheme' )
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
			'name'               => _x( 'Venues', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Venue', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'venue', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Venue', 'audiotheme' ),
			'edit_item'          => __( 'Edit Venue', 'audiotheme' ),
			'new_item'           => __( 'New Venue', 'audiotheme' ),
			'view_item'          => __( 'View Venue', 'audiotheme' ),
			'search_items'       => __( 'Search Venues', 'audiotheme' ),
			'not_found'          => __( 'No venues found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No venues found in Trash', 'audiotheme' ),
			'all_items'          => __( 'All Venues', 'audiotheme' ),
			'menu_name'          => __( 'Venues', 'audiotheme' )
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