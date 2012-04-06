<?php
add_action( 'init', 'audiotheme_register_videos' );
/**
 * Register Video CPT
 *
 * @since 1.0
 */
function audiotheme_register_videos() {

	$labels = array(
		'name'               => __( 'Videos', 'audiotheme' ), 'post type general name',
		'singular_name'      => __( 'Video', 'audiotheme' ), 'post type singular name',
		'add_new'            => __( 'Add New Video', 'audiotheme' ), 'Videos',
		'add_new_item'       => __( 'Add New Video', 'audiotheme' ),
		'edit'               => __( 'Edit Video', 'audiotheme' ),
		'edit_item'          => __( 'Edit Video', 'audiotheme' ),
		'new_item'           => __( 'New Video', 'audiotheme' ),
		'view'               => __( 'View Video', 'audiotheme' ),
		'view_item'          => __( 'View Video', 'audiotheme' ),
		'search_items'       => __( 'Search Videos', 'audiotheme' ),
		'not_found'          => __( 'No Videos found', 'audiotheme' ),
		'not_found_in_trash' => __( 'No Videos found in Trash', 'audiotheme' )
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
		'rewrite'            => array( 'slug' => 'videos', 'with_front' => false ),
		'capability_type'    => 'post',
		'hierarchical'       => false,
    	'menu_position'      => 8,
    	'supports'           => $supports
	);
	
	register_post_type( 'audiotheme_video', $args );

}


add_action( 'init', 'audiotheme_register_video_taxonomies' );
/**
 * Register Video Taxonomies
 *
 * @since 1.0
 */
function audiotheme_register_video_taxonomies() {

	$labels = array(
		'name'                       => __( 'Video Types', 'audiotheme' ), 'taxonomy general name',
		'singular_name'              => __( 'Video Type', 'audiotheme' ), 'taxonomy singular name',
		'search_items'               => __( 'Search Video Types', 'audiotheme' ),
		'popular_items'              => __( 'Popular Video Types', 'audiotheme' ),
		'all_items'                  => __( 'All Video Types', 'audiotheme' ),
		'parent_item'                => __( 'Parent Video Type', 'audiotheme' ),
		'edit_item'                  => __( 'Edit Video Type', 'audiotheme' ),
		'update_item'                => __( 'Update Video Type', 'audiotheme' ),
		'add_new_item'               => __( 'Add New Video Type', 'audiotheme' ),
		'new_item_name'              => __( 'New Video Type', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Video Types with commas', 'audiotheme' ),
		'add_or_remove_items'        => __( 'Add or Remove Video Types', 'audiotheme' ),
		'choose_from_most_used'      => __( 'Choose from Most Used Video Types', 'audiotheme' )
	);
	
	$args = array(
		'label'             => __( 'Video Types', 'audiotheme' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'videos/type', 'with_front' => false ),
		'query_var'         => true
	);
	
	register_taxonomy( 'audiotheme_video_type', 'audiotheme_video', $args );

}


add_action( 'init', 'audiotheme_register_video_tags' );
/**
 * Register Video Tags
 *
 * @since 1.0
 */
function audiotheme_register_video_tags() {

	$labels = array(
		'name'                       => __( 'Video Tags', 'audiotheme' ), 'taxonomy general name',
		'singular_name'              => __( 'Video Tag', 'audiotheme' ), 'taxonomy singular name',
		'search_items'               => __( 'Search Video Tags', 'audiotheme' ),
		'popular_items'              => __( 'Popular Video Tags', 'audiotheme' ),
		'all_items'                  => __( 'All Video Tags', 'audiotheme' ),
		'edit_item'                  => __( 'Edit Video Tag', 'audiotheme' ),
		'update_item'                => __( 'Update Video Tag', 'audiotheme' ),
		'add_new_item'               => __( 'Add New Video Tag', 'audiotheme' ),
		'new_item_name'              => __( 'New Video Tag', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Video Tags with commas', 'audiotheme' ),
		'add_or_remove_items'        => __( 'Add or Remove Video Tags', 'audiotheme' ),
		'choose_from_most_used'      => __( 'Choose from Most Used Video Tags', 'audiotheme' )
	);
	
	$args = array(
		'label'             => __( 'Video Tags', 'audiotheme' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'videos/tags', 'with_front' => false ),
		'query_var'         => true
	);
	
	register_taxonomy( 'audiotheme_video_tag', 'audiotheme_video', $args );
	
}


add_filter( 'post_updated_messages', 'audiotheme_video_updated_messages' );
/**
 * Updated Messages
 *
 * @since 1.0
 */
function audiotheme_video_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['videos'] = array(
		0 => '',
		1 => sprintf( __( 'Video updated. <a href="%s">View Video</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Video updated.', 'audiotheme' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'audiotheme' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Video published. <a href="%s">View Video</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Video saved.', 'audiotheme' ),
		8 => sprintf( __( 'Video submitted. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Video</a>', 'audiotheme' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Video draft updated. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
	
}


add_filter( 'manage_edit-audiotheme_video_columns', 'audiotheme_custom_video_columns' );
/**
 * Custom Video Columns
 *
 * @since 1.0
 */
function audiotheme_custom_video_columns( $video_columns ) {
	
	$video_columns = array(
		'cb'         => '<input type="checkbox" />',
		'title'      => _x( __( 'Video', 'audiotheme' ), 'column name' ),
		'author'     => __( 'Author', 'audiotheme' ),
		'video-type' => __( 'Video Type', 'audiotheme' ),
		'video-tags' => __( 'Video Tags', 'audiotheme' ),
		'date'       => _x( __( 'Date', 'audiotheme' ), 'column name' )
	);
	
	return $video_columns;

}


add_action( 'manage_posts_custom_column', 'audiotheme_video_taxonomy_column' );
/**
 * Video Taxonomy Columns
 *
 * @since 1.0
 */
function audiotheme_video_taxonomy_column( $video_columns ) {
	global $post;
	
	switch ( $video_columns ) {
		case 'video-type' :
			$taxonomy = 'video_type';
			$post_type = get_post_type( $post->ID );
			$video_types = get_the_terms( $post->ID, $taxonomy );
			
			if( ! empty( $video_types ) ) {
				foreach ( $video_types as $video_type ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $video_type->slug ) ),
						esc_html( sanitize_term_field( 'name', $video_type->name, $video_type->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} 
			else {
				echo '<i>' . __( 'No video types.', 'audiotheme' ) . '</i>';
			}
			break;
	}
}


add_action( 'manage_posts_custom_column', 'audiotheme_video_tag_column' );
/**
 * Video Tag Columns
 *
 * @since 1.0
 */
function audiotheme_video_tag_column( $tag_column ) {
	global $post;
	
	switch( $tag_column ) {
		case 'video-tags' :
			$taxonomy = 'video_tag';
			$post_type = get_post_type( $post->ID );
			$video_tags = get_the_terms( $post->ID, $taxonomy );
			
			if( ! empty( $video_tags ) ) {
				foreach ( $video_tags as $video_tag ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $video_tag->slug ) ),
						esc_html( sanitize_term_field( 'name', $video_tag->name, $video_tag->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} 
			else {
				echo '<i>' . __( 'No video tags.', 'audiotheme' ) . '</i>';
			}
			break;
	}
}

// Custom fields
//require_once( AUDIOTHEME_DIR.'metaboxes/video.php' );

?>