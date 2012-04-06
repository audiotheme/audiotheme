<?php
add_action( 'init', 'audiotheme_register_tracks' );
/**
 * Register Track CPT
 *
 * @since 1.0
 */
function audiotheme_register_tracks() {

	$labels = array(
		'name'               => __( 'Tracks', 'audiotheme' ), 'post type general name',
		'singular_name'      => __( 'Track', 'audiotheme' ), 'post type singular name',
		'add_new'            => __( 'Add New Track', 'audiotheme' ), 'Tracks',
		'add_new_item'       => __( 'Add New Track', 'audiotheme' ),
		'edit'               => __( 'Edit Track', 'audiotheme' ),
		'edit_item'          => __( 'Edit Track', 'audiotheme' ),
		'new_item'           => __( 'New Track', 'audiotheme' ),
		'view'               => __( 'View Track', 'audiotheme' ),
		'view_item'          => __( 'View Track', 'audiotheme' ),
		'search_items'       => __( 'Search Track', 'audiotheme' ),
		'not_found'          => __( 'No Tracks found', 'audiotheme' ),
		'not_found_in_trash' => __( 'No Tracks found in Trash', 'audiotheme' )
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
		'rewrite'            => array( 'slug' => 'tracks', 'with_front' => false ),
		'capability_type'    => 'post',
		'hierarchical'       => false,
    	'menu_position'      => 20,
    	'supports'           => $supports
	);
	
	register_post_type( 'audiotheme_track', $args );

}


add_filter( 'post_updated_messages', 'audiotheme_track_updated_messages' );
/**
 * Updated Messages
 *
 * @since 1.0
 */
function audiotheme_track_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['videos'] = array(
		0 => '',
		1 => sprintf( __( 'Track updated. <a href="%s">View Track</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Track updated.', 'audiotheme' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Track restored to revision from %s', 'audiotheme' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Track published. <a href="%s">View Track</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Track saved.', 'audiotheme' ),
		8 => sprintf( __( 'Track submitted. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Track scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Track</a>', 'audiotheme' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Track draft updated. <a target="_blank" href="%s">Preview Track</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
	
}

?>