<?php

/*
Plugin Name: Music Video Library Custom Post Type
Plugin URI: http://www.wearepixel8.com
Description: This plugin will create a custom post type, named Music Video Library, and associated custom taxonomies, named Music Video Type and Music Video Tags.
Version: 1.0
Author: We Are Pixel8
Author URI: http://www.wearepixel8.com
*/

/*-----------------------------------------------------------------------------------*/
/* Register "Music Video Type" taxonomy for the Music Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add action to register "Music Video Type" taxonomy for the Music Video Library CPT
add_action( 'init', 'wap8_create_musicvid_taxonomies' );

function wap8_create_musicvid_taxonomies() {

	$labels = array(
		'name' => __( 'Music Video Types', 'wap8lang' ), 'taxonomy general name',
		'singular_name' => __( 'Music Video Type', 'wap8lang' ), 'taxonomy singular name',
		'search_items' => __( 'Search Music Video Types', 'wap8lang' ),
		'popular_items' => __( 'Popular Music Video Types', 'wap8lang' ),
		'all_items' => __( 'All Music Video Types', 'wap8lang' ),
		'parent_item' => __( 'Parent Music Video Type', 'wap8lang' ),
		'edit_item' => __( 'Edit Music Video Type', 'wap8lang' ),
		'update_item' => __( 'Update Music Video Type', 'wap8lang' ),
		'add_new_item' => __( 'Add New Music Video Type', 'wap8lang' ),
		'new_item_name' => __( 'New Music Video Type', 'wap8lang' ),
		'separate_items_with_commas' => __( 'Separate Music Video Types with commas', 'wap8lang' ),
		'add_or_remove_items' => __( 'Add or Remove Music Video Types', 'wap8lang' ),
		'choose_from_most_used' => __( 'Choose from Most Used Music Video Types', 'wap8lang' )
	);
	
	$args = array(
		'label' => __( 'Music Video Types', 'wap8lang' ),
		'labels' => $labels,
		'public' => true,
		'hierarchical' => true,
		'show_ui' => true,
		'show_in_nav_menus' => true,
		'args' => array( 'orderby' => 'term_order' ),
		'rewrite' => array( 'slug' => 'music-videos/video-type', 'with_front' => false ),
		'query_var' => true
	);
	
	register_taxonomy( 'video-type', 'music-videos', $args );

}

/*-----------------------------------------------------------------------------------*/
/* Register "Music Video Tags" taxonomy for the Music Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add action to register "Music Video Tags" taxonomy for the Music Video Library CPT
add_action( 'init', 'wap8_create_musicvid_tags' );

function wap8_create_musicvid_tags() {

	$labels = array(
		'name' => __( 'Music Video Tags', 'wap8lang' ), 'taxonomy general name',
		'singular_name' => __( 'Music Video Tag', 'wap8lang' ), 'taxonomy singular name',
		'search_items' => __( 'Search Music Video Tags', 'wap8lang' ),
		'popular_items' => __( 'Popular Music Video Tags', 'wap8lang' ),
		'all_items' => __( 'All Music Video Tags', 'wap8lang' ),
		'edit_item' => __( 'Edit Music Video Tag', 'wap8lang' ),
		'update_item' => __( 'Update Music Video Tag', 'wap8lang' ),
		'add_new_item' => __( 'Add New Music Video Tag', 'wap8lang' ),
		'new_item_name' => __( 'New Music Video Tag', 'wap8lang' ),
		'separate_items_with_commas' => __( 'Separate Music Video Tags with commas', 'wap8lang' ),
		'add_or_remove_items' => __( 'Add or Remove Music Video Tags', 'wap8lang' ),
		'choose_from_most_used' => __( 'Choose from Most Used Music Video Tags', 'wap8lang' )
	);
	
	$args = array(
		'label' => __( 'Music Video Tags', 'wap8lang' ),
		'labels' => $labels,
		'public' => true,
		'hierarchical' => false,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud' => false,
		'args' => array( 'orderby' => 'term_order' ),
		'rewrite' => array( 'slug' => 'music-videos/video-tags', 'with_front' => false ),
		'query_var' => true
	);
	
	register_taxonomy( 'video-tags', 'music-videos', $args );
	
}

/*-----------------------------------------------------------------------------------*/
/* Register Music Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add action to register Music Video Library CPT
add_action( 'init', 'wap8_register_music_videos' );

function wap8_register_music_videos() {

	$labels = array(
		'name' => __( 'Music Videos', 'wap8lang' ), 'post type general name',
		'singular_name' => __( 'Music Video', 'wap8lang' ), 'post type singular name',
		'add_new' => __( 'Add New Video', 'wap8lang' ), 'music videos',
		'add_new_item' => __( 'Add New Music Video', 'wap8lang' ),
		'edit' => __( 'Edit Video', 'wap8lang' ),
		'edit_item' => __( 'Edit Music Video', 'wap8lang' ),
		'new_item' => __( 'New Music Video', 'wap8lang' ),
		'view' => __( 'View Video', 'wap8lang' ),
		'view_item' => __( 'View Music Video', 'wap8lang' ),
		'search_items' => __( 'Search Music Videos', 'wap8lang' ),
		'not_found' => __( 'No Music Videos found', 'wap8lang' ),
		'not_found_in_trash' => __( 'No Music Videos found in Trash', 'wap8lang' )
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
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
    	'show_ui' => true, 
    	'show_in_menu' => true, 
    	'query_var' => true,
		'rewrite' => array( 'slug' => 'music-videos', 'with_front' => false ),
		'capability_type' => 'post',
		'hierarchical' => false,
    	'menu_position' => 20,
    	'supports' => $supports
	);
	
	register_post_type( 'music-videos', $args );

}

/*-----------------------------------------------------------------------------------*/
/* Contextual update messages for Music Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add filter display contextual custom post type messages 
add_filter( 'post_updated_messages', 'wap8_musicvid_updated_messages' );

function wap8_musicvid_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['music-videos'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Music Video updated. <a href="%s">View music video</a>', 'wap8lang' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'wap8lang' ),
		3 => __( 'Custom field deleted.', 'wap8lang' ),
		4 => __( 'Music Video updated.', 'wap8lang' ),
		/* translators: %s: date and time of the revision */
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Music Video restored to revision from %s', 'wap8lang' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Music Video published. <a href="%s">View music video</a>', 'wap8lang' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Music Video saved.', 'wap8lang' ),
		8 => sprintf( __( 'Music Video submitted. <a target="_blank" href="%s">Preview music video</a>', 'wap8lang' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Music Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview music video</a>', 'wap8lang' ),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i', 'wap8lang' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Music Video draft updated. <a target="_blank" href="%s">Preview music video</a>', 'wap8lang' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
	
}

/*-----------------------------------------------------------------------------------*/
/* Add custom columns for Music Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add filter to display Music Video Library custom columns
add_filter( 'manage_edit-music-videos_columns', 'wap8_custom_musicvid_columns' );

function wap8_custom_musicvid_columns( $video_columns ) {
	$video_columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => _x( __( 'Music Video', 'wap8lang' ), 'column name' ),
		'author' => __( 'Author', 'wap8lang' ),
		'video-type' => __( 'Video Type', 'wap8lang' ),
		'video-tags' => __( 'Video Tags', 'wap8lang' ),
		'date' => _x( __( 'Date', 'wap8lang' ), 'column name' )
	);
	return $video_columns;

}

/*-----------------------------------------------------------------------------------*/
/* Show "Music Video Type" in the Music Video Library CPT custom columns
/*-----------------------------------------------------------------------------------*/

// Add action to display "Music Video Type"
add_action( 'manage_posts_custom_column', 'wap8_musicvid_taxonomy_column' );

function wap8_musicvid_taxonomy_column( $video_columns ) {
	global $post;
	
	switch ( $video_columns ) {
		case 'video-type' :
			$taxonomy = __( 'video-type', 'wap8lang' );
			$post_type = get_post_type( $post->ID );
			$vid_types = get_the_terms( $post->ID, $taxonomy );
			if ( !empty( $vid_types ) ) {
				foreach ( $vid_types as $vid_type )
				$post_terms[] = "<a href=\"edit.php?post_type={$post_type}&{$taxonomy}={$vid_type->slug}\">" . esc_html( sanitize_term_field( 'name', $vid_type->name, $vid_type->term_id, $taxonomy, 'edit' ) ) . '</a>';
				echo join( ', ', $post_terms );
			} else echo __( '<i>No video types.</i>', 'wap8lang' );
			
		break;
	}
}

/*-----------------------------------------------------------------------------------*/
/* Show "Music Video Tags" in the Music Video Library CPT custom columns
/*-----------------------------------------------------------------------------------*/

// Add action to display "Music Video Tags"
add_action( 'manage_posts_custom_column', 'wap8_musicvid_tag_column' );

function wap8_musicvid_tag_column( $tag_column ) {
	global $post;
	
	switch ( $tag_column ) {
		case 'video-tags' :
			$taxonomy = __( 'video-tags', 'wap8lang' );
			$post_type = get_post_type( $post->ID );
			$video_tags = get_the_terms( $post->ID, $taxonomy );
			if ( !empty( $video_tags ) ) {
				foreach ( $video_tags as $video_tag )
				$post_terms[] = "<a href=\"edit.php?post_type={$post_type}&{$taxonomy}={$video_tag->slug}\">" . esc_html( sanitize_term_field( 'name', $video_tag->name, $video_tag->term_id, $taxonomy, 'edit' ) ) . "</a>";
				echo join( ', ', $post_terms );
			} else echo __( '<i>No video tags.</i>', 'wap8lang' );
			break;
	}

}

/*-----------------------------------------------------------------------------------*/
/* Add a custom meta box to Music Video Library CPT editor screen
/*-----------------------------------------------------------------------------------*/

// Add the meta box
add_action( 'add_meta_boxes', 'wap8_add_video_meta' );

function wap8_add_video_meta() {
	add_meta_box( 'wap8-video-meta', __( 'Music Video Library: Add Video URL', 'wap8lang' ), 'wap8_video_meta_cb', 'music-videos', 'normal', 'high' );
}

// Render the meta box
function wap8_video_meta_cb( $post ) {

	// Store the saved values
	$video = get_post_meta( $post->ID, '_wap8_video_url', true );

	// Nonce to verify intention later
	wp_nonce_field( 'save_wap8_video_meta', 'wap8_video_nonce' );

	?>
	
	<p>
		<label for="wap8-video-url"><?php _e( 'Video URL', 'wap8lang' ); ?></label><br />
		<input type="text" id="wap8-video-url" style="width: 400px; margin-right: 5px;" name="_wap8_video_url" value="<?php echo esc_url( $video ); ?>" />
		<?php _e( '<strong>Important:</strong> Make sure the video service allows content embedding with oEmbed. ', 'wap8lang' ); ?>
	</p>

	<?php
	
	// show video preview if an URL is set
	if ( $video ) {
		echo '<p>' . __( '<strong>Video Preview</strong>', 'wap8lang' ) . '</p>';
		echo wp_oembed_get( $video, array( 'width' => 570 ) );
	}

}

// Save meta box
add_action( 'save_post', 'wap8_video_save' );

function wap8_video_save( $id ) {

	// Let's not auto save the data
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 

	// Check our nonce
	if( !isset( $_POST['wap8_video_nonce'] ) || !wp_verify_nonce( $_POST['wap8_video_nonce'], 'save_wap8_video_meta' ) ) return;

	// Make sure the current user can edit the post
	if( !current_user_can( 'edit_post' ) ) return;

	// Make sure we get a clean url here with esc_url
	if( isset( $_POST['_wap8_video_url'] ) )
		update_post_meta( $id, '_wap8_video_url', esc_url( $_POST['_wap8_video_url'], array( 'http' ) ) );
}

?>