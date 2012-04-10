<?php
add_action( 'init', 'audiotheme_gigs_init' );

function audiotheme_gigs_init() {
	register_post_type( 'audiotheme_gig', array(
		'has_archive' => 'tour',
		'hierarchical' => false,
		'labels' => array(
			'name' => 'Gigs',
			'singular_name' => 'Gig',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Gig',
			'edit_item' => 'Edit Gig',
			'new_item' => 'New Gig',
			'view_item' => 'View Gig',
			'search_items' => 'Search Gigs',
			'not_found' => 'No gigs found.',
			'not_found_in_trash' => 'No gigs found in Trash.',
			'all_items' => 'All Gigs',
			'menu_name' => 'Gigs'
		),
		'menu_position' => 6,
		'public' => true,
		'register_meta_box_cb' => 'audiotheme_edit_gig_meta_boxes',
		'rewrite' => false, //array( 'slug' => 'tour/%year%', 'with_front' => false ),
		'show_in_menu' => 'gigs',
		'supports' => array( 'title', 'editor', 'thumbnail', '' )
	) );
	
	register_post_type( 'audiotheme_venue', array(
		'has_archive' => false,
		'hierarchical' => false,
		'labels' => array(
			'name' => 'Venues',
			'singular_name' => 'Venue',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Venue',
			'edit_item' => 'Edit Venue',
			'new_item' => 'New Venue',
			'view_item' => 'View Venue',
			'search_items' => 'Search Venues',
			'not_found' => 'No venues found.',
			'not_found_in_trash' => 'No venues found in Trash.',
			'all_items' => 'All Venues',
			'menu_name' => 'Venues'
		),
		'public' => false,
		'publicly_queryable' => false,
		'query_var' => 'audiotheme_venue',
		'rewrite' => false,
		'supports' => array( '' )
	) );
}


include AUDIOTHEME_DIR . 'gigs/general-template.php';

if ( is_admin() ) {
	include AUDIOTHEME_DIR . 'gigs/admin/gigs.php';
	include AUDIOTHEME_DIR . 'gigs/admin/venues.php';
}
?>