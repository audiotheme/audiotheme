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
		1 => sprintf( __( 'Gallery updated. <a href="%s">View Gallery</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Gallery updated.', 'audiotheme' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Gallery restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Gallery published. <a href="%s">View Gallery</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Gallery saved.', 'audiotheme' ),
		8 => sprintf( __( 'Gallery submitted. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Gallery scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Gallery</a>', 'audiotheme' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Gallery draft updated. <a target="_blank" href="%s">Preview Gallery</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);
	
	$messages['audiotheme_record'] = array(
		0 => '',
		1 => sprintf( __( 'Record updated. <a href="%s">View Record</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Record updated.', 'audiotheme' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Record restored to revision from %s', 'audiotheme' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Record published. <a href="%s">View Record</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Record saved.', 'audiotheme' ),
		8 => sprintf( __( 'Record submitted. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Record scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Record</a>', 'audiotheme' ), date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Record draft updated. <a target="_blank" href="%s">Preview Record</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);
	
	$messages['audiotheme_track'] = array(
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
	
	$messages['audiotheme_video'] = array(
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

/**
 * Register Record Columns
 *
 * @since 1.0
 */
function audiotheme_record_columns( $columns ) {
	$columns = array(
		'cb'          => '<input type="checkbox">',
		'title'       => _x( 'Record', 'column_name', 'audiotheme' ),
		'author'      => __( 'Author', 'audiotheme' ),
		'record_type' => __( 'Type', 'audiotheme' ),
		'tags'        => __( 'Tags', 'audiotheme' ),
		'date'        => __( 'Date', 'audiotheme' )
	);
	
	return $columns;
}

/**
 * Register Video Columns
 *
 * @since 1.0
 */
function audiotheme_video_columns( $columns ) {
	$columns = array(
		'cb'         => '<input type="checkbox">',
		'title'      => _x( 'Video', 'column name', 'audiotheme' ),
		'author'     => __( 'Author', 'audiotheme' ),
		'video_type' => __( 'Type', 'audiotheme' ),
		'tags'       => __( 'Tags', 'audiotheme' ),
		'date'       => __( 'Date', 'audiotheme' )
	);
	
	return $columns;
}

/**
 * Custom Post Type Columns
 *
 * @since 1.0
 */
function audiotheme_display_custom_column( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'record_type' :
			$taxonomy = 'audiotheme_record_type';
			$post_type = get_post_type( $post_id );
			$record_types = get_the_terms( $post_id, $taxonomy );
			
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
				echo '<em>' . __( 'No record types.', 'audiotheme' ) . '</em>';
			}
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
			} 
			else {
				echo '<em>' . __( 'No video types.', 'audiotheme' ) . '</em>';
			}
			break;
	}
}
?>