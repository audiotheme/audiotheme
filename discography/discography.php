<?php
/**
 * Gigs Init
 *
 * @since 1.0
 */
add_action( 'init', 'audiotheme_discography_init' );
function audiotheme_discography_init() {
	register_post_type( 'audiotheme_record', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => true,
		'labels'                 => array(
			'name'               => _x( 'Records', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Record', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'record', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Record', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Record', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Record', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Record', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Records', 'audiotheme-i18n' ),
			'not_found'          => __( 'No records found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No records found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'Records', 'audiotheme-i18n' )
		),
		'menu_position'          => 7,
		'public'                 => true,
		'publicly_queryable'     => true,
		'register_meta_box_cb'   => 'audiotheme_edit_record_meta_boxes',
		'rewrite'                => false,
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail' ),
		#'taxonomies'             => array( 'post_tag' )
	) );
	
	register_post_type( 'audiotheme_track', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Tracks', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Track', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'track', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Track', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Track', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Track', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Track', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Tracks', 'audiotheme-i18n' ),
			'not_found'          => __( 'No tracks found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No tracks found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'Tracks', 'audiotheme-i18n' )
		),
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => false,
		'show_ui'                => true,
		'show_in_menu'           => 'edit.php?post_type=audiotheme_record', // TODO: set to false before release
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' )
	) );
	
	register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', array(
		'args'                           => array( 'orderby' => 'term_order' ),
		'hierarchical'                   => true,
		'labels'                         => array(
			'name'                       => _x( 'Record Types', 'taxonomy general name', 'audiotheme-i18n' ),
			'singular_name'              => _x( 'Record Type', 'taxonomy singular name', 'audiotheme-i18n' ),
			'search_items'               => __( 'Search Record Types', 'audiotheme-i18n' ),
			'popular_items'              => __( 'Popular Record Types', 'audiotheme-i18n' ),
			'all_items'                  => __( 'All Record Types', 'audiotheme-i18n' ),
			'parent_item'                => __( 'Parent Record Type', 'audiotheme-i18n' ),
			'parent_item_colon'          => __( 'Parent Record Type:', 'audiotheme-i18n' ),
			'edit_item'                  => __( 'Edit Record Type', 'audiotheme-i18n' ),
			'view_item'                  => __( 'View Record Type', 'audiotheme-i18n' ),
			'update_item'                => __( 'Update Record Type', 'audiotheme-i18n' ),
			'add_new_item'               => __( 'Add New Record Type', 'audiotheme-i18n' ),
			'new_item_name'              => __( 'New Record Type Name', 'audiotheme-i18n' ),
			'separate_items_with_commas' => __( 'Separate record types with commas', 'audiotheme-i18n' ),
			'add_or_remove_items'        => __( 'Add or remove record types', 'audiotheme-i18n' ),
			'choose_from_most_used'      => __( 'Choose from most used record types', 'audiotheme-i18n' )
		),
		'public'                         => true,
		'query_var'                      => true,
		'rewrite'                        => false,
		'show_ui'                        => true,
		'show_in_nav_menus'              => true
	) );
	
	#add_rewrite_tag( '%audiotheme_record%', '([^/]+)' );
	add_filter( 'generate_rewrite_rules', 'audiotheme_discography_generate_rewrite_rules' );
	add_filter( 'post_type_link', 'audiotheme_discography_permalinks', 10, 4 );
}

function get_audiotheme_discography_rewrite_base() {
	$base = get_option( 'audiotheme_discography_rewrite_base' );
	return ( empty( $base ) ) ? 'record' : $base;
}

function audiotheme_discography_generate_rewrite_rules( $wp_rewrite ) {
	$base = get_audiotheme_discography_rewrite_base();
	
	$new_rules[ $base .'/([^/]+)/track/([^/]+)?$'] = 'index.php?audiotheme_record=$matches[1]&audiotheme_track=$matches[2]';
	$new_rules[ $base . '/([^/]+)/?$'] = 'index.php?audiotheme_record=$matches[1]';
	
	$wp_rewrite->rules = array_merge( $new_rules, $wp_rewrite->rules );
}

function audiotheme_discography_permalinks( $post_link, $post, $leavename, $sample ) {
	global $wpdb;
	
	if ( 'audiotheme_record' == get_post_type( $post ) ) {
		$base = get_audiotheme_discography_rewrite_base();
		$post_link = home_url( sprintf( '/%s/%s/', $base, $post->post_name ) );
	}
	
	if ( 'audiotheme_track' == get_post_type( $post ) && ! empty( $post->post_parent ) ) {
		$base = get_audiotheme_discography_rewrite_base();
		// test to see which performs better
		#$record_slug = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE ID=%d", $post->post_parent ) );
		$record = get_post( $post->post_parent );
		$post_link = home_url( sprintf( '/%s/%s/track/%s/', $base, $record->post_name, $post->post_name ) );
	}
	
	return $post_link;
}

if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'discography/admin/discography.php' );
}
?>