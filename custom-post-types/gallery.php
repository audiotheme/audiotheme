<?php
add_action( 'init', 'audiotheme_register_galleries' );
/**
 * Register Gallery CPT
 *
 * @since 1.0
 */
function audiotheme_register_galleries() {

	$labels = array(
		'name'               => __( 'Galleries', 'audiotheme' ), 'post type general name',
		'singular_name'      => __( 'Gallery', 'audiotheme' ), 'post type singular name',
		'add_new'            => __( 'Add New Gallery', 'audiotheme' ), 'Galleries',
		'add_new_item'       => __( 'Add New Gallery', 'audiotheme' ),
		'edit'               => __( 'Edit Gallery', 'audiotheme' ),
		'edit_item'          => __( 'Edit Gallery', 'audiotheme' ),
		'new_item'           => __( 'New Gallery', 'audiotheme' ),
		'view'               => __( 'View Gallery', 'audiotheme' ),
		'view_item'          => __( 'View Gallery', 'audiotheme' ),
		'search_items'       => __( 'Search Gallery', 'audiotheme' ),
		'not_found'          => __( 'No Galleries found', 'audiotheme' ),
		'not_found_in_trash' => __( 'No Galleries found in Trash', 'audiotheme' )
	);
	
	$supports = array(
		'title',
		'editor',
		'thumbnail',
		'excerpt',
		'revisions',
		'author'
	);
	
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
    	'show_ui'            => true, 
    	'show_in_menu'       => true, 
    	'query_var'          => true,
		'rewrite'            => array( 'slug' => 'galleries', 'with_front' => false ),
		'capability_type'    => 'post',
		'hierarchical'       => false,
    	'menu_position'      => 20,
    	'supports'           => $supports
	);
	
	register_post_type( 'audiotheme_gallery', $args );

}


add_filter( 'post_updated_messages', 'audiotheme_gallery_updated_messages' );
/**
 * Updated Messages
 *
 * @since 1.0
 */
function audiotheme_gallery_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['videos'] = array(
		0 => '',
		1 => sprintf( __( 'Gallery updated. <a href="%s">View Gallery</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Gallery updated.', 'audiotheme' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Gallery restored to revision from %s', 'audiotheme' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Gallery published. <a href="%s">View Gallery</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Gallery saved.', 'audiotheme' ),
		8 => sprintf( __( 'Gallery submitted. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Gallery</a>', 'audiotheme' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Gallery draft updated. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
	
}

?>