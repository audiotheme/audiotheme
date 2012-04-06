<?php
add_action( 'init', 'audiotheme_register_records' );
/**
 * Register Records CPT
 *
 * @since 1.0
 */
function audiotheme_register_records() {

	$labels = array(
		'name'               => __( 'Records', 'audiotheme' ), 'post type general name',
		'singular_name'      => __( 'Record', 'audiotheme' ), 'post type singular name',
		'add_new'            => __( 'Add New Record', 'audiotheme' ), 'Records',
		'add_new_item'       => __( 'Add New Record', 'audiotheme' ),
		'edit'               => __( 'Edit Record', 'audiotheme' ),
		'edit_item'          => __( 'Edit Record', 'audiotheme' ),
		'new_item'           => __( 'New Record', 'audiotheme' ),
		'view'               => __( 'View Record', 'audiotheme' ),
		'view_item'          => __( 'View Record', 'audiotheme' ),
		'search_items'       => __( 'Search Records', 'audiotheme' ),
		'not_found'          => __( 'No Records found', 'audiotheme' ),
		'not_found_in_trash' => __( 'No Records found in Trash', 'audiotheme' )
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
		'rewrite'            => array( 'slug' => 'records', 'with_front' => false ),
		'capability_type'    => 'post',
		'hierarchical'       => false,
    	'menu_position'      => 20,
    	'supports'           => $supports
	);
	
	register_post_type( 'audiotheme_record', $args );

}


add_action( 'init', 'audiotheme_register_record_taxonomies' );
/**
 * Register Records Taxonomies
 *
 * @since 1.0
 */
function audiotheme_register_record_taxonomies() {

	$labels = array(
		'name'                       => __( 'Record Types', 'audiotheme' ), 'taxonomy general name',
		'singular_name'              => __( 'Record Type', 'audiotheme' ), 'taxonomy singular name',
		'search_items'               => __( 'Search Record Types', 'audiotheme' ),
		'popular_items'              => __( 'Popular Record Types', 'audiotheme' ),
		'all_items'                  => __( 'All Record Types', 'audiotheme' ),
		'parent_item'                => __( 'Parent Record Type', 'audiotheme' ),
		'edit_item'                  => __( 'Edit Record Type', 'audiotheme' ),
		'update_item'                => __( 'Update Record Type', 'audiotheme' ),
		'add_new_item'               => __( 'Add New Record Type', 'audiotheme' ),
		'new_item_name'              => __( 'New Record Type', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Record Types with commas', 'audiotheme' ),
		'add_or_remove_items'        => __( 'Add or Remove Record Types', 'audiotheme' ),
		'choose_from_most_used'      => __( 'Choose from Most Used Record Types', 'audiotheme' )
	);
	
	$args = array(
		'label'             => __( 'Record Types', 'audiotheme' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'records/type', 'with_front' => false ),
		'query_var'         => true
	);
	
	register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', $args );

}


add_action( 'init', 'audiotheme_register_record_tags' );
/**
 * Register Record Tags
 *
 * @since 1.0
 */
function audiotheme_register_record_tags() {

	$labels = array(
		'name'                       => __( 'Record Tags', 'audiotheme' ), 'taxonomy general name',
		'singular_name'              => __( 'Record Tag', 'audiotheme' ), 'taxonomy singular name',
		'search_items'               => __( 'Search Record Tags', 'audiotheme' ),
		'popular_items'              => __( 'Popular Record Tags', 'audiotheme' ),
		'all_items'                  => __( 'All Record Tags', 'audiotheme' ),
		'edit_item'                  => __( 'Edit Record Tag', 'audiotheme' ),
		'update_item'                => __( 'Update Record Tag', 'audiotheme' ),
		'add_new_item'               => __( 'Add New Record Tag', 'audiotheme' ),
		'new_item_name'              => __( 'New Record Tag', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Record Tags with commas', 'audiotheme' ),
		'add_or_remove_items'        => __( 'Add or Remove Record Tags', 'audiotheme' ),
		'choose_from_most_used'      => __( 'Choose from Most Used Record Tags', 'audiotheme' )
	);
	
	$args = array(
		'label'             => __( 'Record Tags', 'audiotheme' ),
		'labels'            => $labels,
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'records/tags', 'with_front' => false ),
		'query_var'         => true
	);
	
	register_taxonomy( 'audiotheme_record_tag', 'audiotheme_record', $args );
	
}


add_filter( 'post_updated_messages', 'audiotheme_record_updated_messages' );
/**
 * Updated Messages
 *
 * @since 1.0
 */
function audiotheme_record_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['records'] = array(
		0 => '',
		1 => sprintf( __( 'Record updated. <a href="%s">View Record</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Record updated.', 'audiotheme' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Record restored to revision from %s', 'audiotheme' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Record published. <a href="%s">View Record</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Record saved.', 'audiotheme' ),
		8 => sprintf( __( 'Record submitted. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Record scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Record</a>', 'audiotheme' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Record draft updated. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
	
}


add_filter( 'manage_edit-audiotheme_record_columns', 'audiotheme_custom_record_columns' );
/**
 * Custom Records Columns
 *
 * @since 1.0
 */
function audiotheme_custom_record_columns( $record_columns ) {
	
	$record_columns = array(
		'cb'         => '<input type="checkbox" />',
		'title'      => _x( __( 'Record', 'audiotheme' ), 'column name' ),
		'author'     => __( 'Author', 'audiotheme' ),
		'record-type' => __( 'Record Type', 'audiotheme' ),
		'record-tags' => __( 'Record Tags', 'audiotheme' ),
		'date'       => _x( __( 'Date', 'audiotheme' ), 'column name' )
	);
	
	return $record_columns;

}


add_action( 'manage_posts_custom_column', 'audiotheme_record_taxonomy_column' );
/**
 * Records Taxonomy Columns
 *
 * @since 1.0
 */
function audiotheme_record_taxonomy_column( $record_columns ) {
	global $post;
	
	switch ( $record_columns ) {
		case 'record-type' :
			$taxonomy = 'audiotheme_record_type';
			$post_type = get_post_type( $post->ID );
			$record_types = get_the_terms( $post->ID, $taxonomy );
			
			if( ! empty( $record_types ) ) {
				foreach ( $record_types as $record_type ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $record_type->slug ) ),
						esc_html( sanitize_term_field( 'name', $record_type->name, $record_type->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} 
			else {
				echo '<i>' . __( 'No record types.', 'audiotheme' ) . '</i>';
			}
			break;
	}
}


add_action( 'manage_posts_custom_column', 'audiotheme_record_tag_column' );
/**
 * Record Tag Columns
 *
 * @since 1.0
 */
function audiotheme_record_tag_column( $tag_column ) {
	global $post;
	
	switch( $tag_column ) {
		case 'record-tags' :
			$taxonomy = 'audiotheme_record_tag';
			$post_type = get_post_type( $post->ID );
			$record_tags = get_the_terms( $post->ID, $taxonomy );
			
			if( ! empty( $record_tags ) ) {
				foreach ( $record_tags as $record_tag ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $record_tag->slug ) ),
						esc_html( sanitize_term_field( 'name', $record_tag->name, $record_tag->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} 
			else {
				echo '<i>' . __( 'No record tags.', 'audiotheme' ) . '</i>';
			}
			break;
	}
}
?>