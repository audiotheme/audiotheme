<?php
add_action( 'init', 'audiotheme_register_discogrpahy' );
/**
 * Register Discography CPT
 *
 * @since 1.0
 */
function audiotheme_register_discography() {

	$labels = array(
		'name'               => __( 'Discography', 'audiotheme' ), 'post type general name',
		'singular_name'      => __( 'Disc', 'audiotheme' ), 'post type singular name',
		'add_new'            => __( 'Add New Disc', 'audiotheme' ), 'Discs',
		'add_new_item'       => __( 'Add New Disc', 'audiotheme' ),
		'edit'               => __( 'Edit Disc', 'audiotheme' ),
		'edit_item'          => __( 'Edit Disc', 'audiotheme' ),
		'new_item'           => __( 'New Disc', 'audiotheme' ),
		'view'               => __( 'View Disc', 'audiotheme' ),
		'view_item'          => __( 'View Disc', 'audiotheme' ),
		'search_items'       => __( 'Search Discs', 'audiotheme' ),
		'not_found'          => __( 'No Discs found', 'audiotheme' ),
		'not_found_in_trash' => __( 'No Discs found in Trash', 'audiotheme' )
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
		'rewrite'            => array( 'slug' => 'discography', 'with_front' => false ),
		'capability_type'    => 'post',
		'hierarchical'       => false,
    	'menu_position'      => 20,
    	'supports'           => $supports
	);
	
	register_post_type( 'audiotheme_discography', $args );

}


add_action( 'init', 'audiotheme_register_discography_taxonomies' );
/**
 * Register Video Taxonomies
 *
 * @since 1.0
 */
function audiotheme_register_discography_taxonomies() {

	$labels = array(
		'name'                       => __( 'Discography Types', 'audiotheme' ), 'taxonomy general name',
		'singular_name'              => __( 'Disc Type', 'audiotheme' ), 'taxonomy singular name',
		'search_items'               => __( 'Search Disc Types', 'audiotheme' ),
		'popular_items'              => __( 'Popular Disc Types', 'audiotheme' ),
		'all_items'                  => __( 'All Disc Types', 'audiotheme' ),
		'parent_item'                => __( 'Parent Disc Type', 'audiotheme' ),
		'edit_item'                  => __( 'Edit Disc Type', 'audiotheme' ),
		'update_item'                => __( 'Update Disc Type', 'audiotheme' ),
		'add_new_item'               => __( 'Add New Disc Type', 'audiotheme' ),
		'new_item_name'              => __( 'New Disc Type', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Discography Types with commas', 'audiotheme' ),
		'add_or_remove_items'        => __( 'Add or Remove Discography Types', 'audiotheme' ),
		'choose_from_most_used'      => __( 'Choose from Most Used Discography Types', 'audiotheme' )
	);
	
	$args = array(
		'label'             => __( 'Disc Types', 'audiotheme' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'discography/type', 'with_front' => false ),
		'query_var'         => true
	);
	
	register_taxonomy( 'audiotheme_discography_type', 'audiotheme_discography', $args );

}


add_action( 'init', 'audiotheme_register_discography_tags' );
/**
 * Register Discography Tags
 *
 * @since 1.0
 */
function audiotheme_register_discography_tags() {

	$labels = array(
		'name'                       => __( 'Discography Tags', 'audiotheme' ), 'taxonomy general name',
		'singular_name'              => __( 'Disc Tag', 'audiotheme' ), 'taxonomy singular name',
		'search_items'               => __( 'Search Disc Tags', 'audiotheme' ),
		'popular_items'              => __( 'Popular Disc Tags', 'audiotheme' ),
		'all_items'                  => __( 'All Disc Tags', 'audiotheme' ),
		'edit_item'                  => __( 'Edit Disc Tag', 'audiotheme' ),
		'update_item'                => __( 'Update Disc Tag', 'audiotheme' ),
		'add_new_item'               => __( 'Add New Disc Tag', 'audiotheme' ),
		'new_item_name'              => __( 'New Disc Tag', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Discography Tags with commas', 'audiotheme' ),
		'add_or_remove_items'        => __( 'Add or Remove Discography Tags', 'audiotheme' ),
		'choose_from_most_used'      => __( 'Choose from Most Used Discography Tags', 'audiotheme' )
	);
	
	$args = array(
		'label'             => __( 'Discography Tags', 'audiotheme' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'discography/tags', 'with_front' => false ),
		'query_var'         => true
	);
	
	register_taxonomy( 'audiotheme_discography_tag', 'audiotheme_discography', $args );
	
}


add_filter( 'post_updated_messages', 'audiotheme_disc_updated_messages' );
/**
 * Updated Messages
 *
 * @since 1.0
 */
function audiotheme_disc_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['discography'] = array(
		0 => '',
		1 => sprintf( __( 'Disc updated. <a href="%s">View Disc</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Disc updated.', 'audiotheme' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Disc restored to revision from %s', 'audiotheme' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Disc published. <a href="%s">View Disc</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Disc saved.', 'audiotheme' ),
		8 => sprintf( __( 'Disc submitted. <a target="_blank" href="%s">Preview Disc</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Disc scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Disc</a>', 'audiotheme' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Disc draft updated. <a target="_blank" href="%s">Preview Disc</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
	
}


add_filter( 'manage_edit-audiotheme_discography_columns', 'audiotheme_custom_discography_columns' );
/**
 * Custom Discography Columns
 *
 * @since 1.0
 */
function audiotheme_custom_discography_columns( $discography_columns ) {
	
	$discography_columns = array(
		'cb'         => '<input type="checkbox" />',
		'title'      => _x( __( 'Disc', 'audiotheme' ), 'column name' ),
		'author'     => __( 'Author', 'audiotheme' ),
		'video-type' => __( 'Disc Type', 'audiotheme' ),
		'video-tags' => __( 'Disc Tags', 'audiotheme' ),
		'date'       => _x( __( 'Date', 'audiotheme' ), 'column name' )
	);
	
	return $discography_columns;

}


add_action( 'manage_posts_custom_column', 'audiotheme_discography_taxonomy_column' );
/**
 * Video Taxonomy Columns
 *
 * @since 1.0
 */
function audiotheme_discography_taxonomy_column( $discography_columns ) {
	global $post;
	
	switch ( $discography_columns ) {
		case 'disc-type' :
			$taxonomy = 'audiotheme_discography_type';
			$post_type = get_post_type( $post->ID );
			$disc_types = get_the_terms( $post->ID, $taxonomy );
			
			if( ! empty( $disc_types ) ) {
				foreach ( $disc_types as $disc_type ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $disc_type->slug ) ),
						esc_html( sanitize_term_field( 'name', $disc_type->name, $disc_type->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} 
			else {
				echo '<i>' . __( 'No discography types.', 'audiotheme' ) . '</i>';
			}
			break;
	}
}


add_action( 'manage_posts_custom_column', 'audiotheme_discography_tag_column' );
/**
 * Video Tag Columns
 *
 * @since 1.0
 */
function audiotheme_discography_tag_column( $tag_column ) {
	global $post;
	
	switch( $tag_column ) {
		case 'disc-tags' :
			$taxonomy = 'audiotheme_discography_tag';
			$post_type = get_post_type( $post->ID );
			$disc_tags = get_the_terms( $post->ID, $taxonomy );
			
			if( ! empty( $disc_tags ) ) {
				foreach ( $disc_tags as $disc_tag ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $disc_tag->slug ) ),
						esc_html( sanitize_term_field( 'name', $disc_tag->name, $disc_tag->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} 
			else {
				echo '<i>' . __( 'No discography tags.', 'audiotheme' ) . '</i>';
			}
			break;
	}
}
?>