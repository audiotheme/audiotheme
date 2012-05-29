<?php
/**
 * Custom Post Type Update Messages
 *
 * @since 1.0
 */
function audiotheme_post_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['audiotheme_gallery'] = array(
		0 => '',
		1 => sprintf( __( 'Gallery updated. <a href="%s">View Gallery</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3 => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4 => __( 'Gallery updated.', 'audiotheme-i18n' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Gallery restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Gallery published. <a href="%s">View Gallery</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Gallery saved.', 'audiotheme-i18n' ),
		8 => sprintf( __( 'Gallery submitted. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Gallery</a>', 'audiotheme-i18n' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Gallery draft updated. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);
	
	$messages['audiotheme_video'] = array(
		0 => '',
		1 => sprintf( __( 'Video updated. <a href="%s">View Video</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme-i18n' ),
		3 => __( 'Custom field deleted.', 'audiotheme-i18n' ),
		4 => __( 'Video updated.', 'audiotheme-i18n' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'audiotheme-i18n' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Video published. <a href="%s">View Video</a>', 'audiotheme-i18n' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Video saved.', 'audiotheme-i18n' ),
		8 => sprintf( __( 'Video submitted. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Video</a>', 'audiotheme-i18n' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme-i18n' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Video draft updated. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme-i18n' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
}

/**
 * Register Video Columns
 *
 * @since 1.0
 */
function audiotheme_video_columns( $columns ) {
	$columns = array(
		'cb'         => '<input type="checkbox">',
		'image'      => __( 'Image', 'audiotheme-i18n' ),
		'title'      => _x( 'Video', 'column name', 'audiotheme-i18n' ),
		'author'     => __( 'Author', 'audiotheme-i18n' ),
		'video_type' => __( 'Type', 'audiotheme-i18n' ),
		'tags'       => __( 'Tags', 'audiotheme-i18n' ),
		'date'       => __( 'Date', 'audiotheme-i18n' )
	);
	
	return $columns;
}

/**
 * Custom Post Type Columns
 *
 * @since 1.0
 */
function audiotheme_display_custom_column( $column_name, $post_id ) {

	global $post;
	
	/* Get the post edit link for the post. */
	$edit_link = get_edit_post_link( $post->ID );
	
	switch ( $column_name ) {
		case 'image' :
			printf( '<a href="%1$s" title="%2$s">%3$s</a>', 
				esc_url( $edit_link ),
				esc_attr( $post->post_title ),
				get_the_post_thumbnail( $post->ID, array( 60, 60 ), array( 'title' => trim( strip_tags(  $post->post_title ) ) ) )
			);
			break;
			
		case 'video_type' :
			$taxonomy = 'audiotheme_video_type';
			$post_type = get_post_type( $post_id );
			$video_types = get_the_terms( $post_id, $taxonomy );
			
			if( ! empty( $video_types ) ) {
				foreach ( $video_types as $video_type ) {
					$post_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( sprintf( 'edit.php?post_type=%1$s&%2$s=%3$s', $post_type, $taxonomy, $video_type->slug ) ),
						esc_html( sanitize_term_field( 'name', $video_type->name, $video_type->term_id, $taxonomy, 'edit' ) )
					);
				}
				
				echo join( ', ', $post_terms );
			} else {
				echo '<em>' . __( 'No video types.', 'audiotheme-i18n' ) . '</em>';
			}
			break;
	}
}
?>