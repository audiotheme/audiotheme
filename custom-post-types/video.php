<?php
/*-----------------------------------------------------------------------------------*/
/* Register "Video Type" taxonomy for the Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add action to register "Video Type" taxonomy for the Video Library CPT
add_action( 'init', 'audiotheme_register_video_taxonomies' );

function audiotheme_register_video_taxonomies() {

	$labels = array(
		'name' => __( 'Video Types', 'audiotheme' ), 'taxonomy general name',
		'singular_name' => __( 'Video Type', 'audiotheme' ), 'taxonomy singular name',
		'search_items' => __( 'Search Video Types', 'audiotheme' ),
		'popular_items' => __( 'Popular Video Types', 'audiotheme' ),
		'all_items' => __( 'All Video Types', 'audiotheme' ),
		'parent_item' => __( 'Parent Video Type', 'audiotheme' ),
		'edit_item' => __( 'Edit Video Type', 'audiotheme' ),
		'update_item' => __( 'Update Video Type', 'audiotheme' ),
		'add_new_item' => __( 'Add New Video Type', 'audiotheme' ),
		'new_item_name' => __( 'New Video Type', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Video Types with commas', 'audiotheme' ),
		'add_or_remove_items' => __( 'Add or Remove Video Types', 'audiotheme' ),
		'choose_from_most_used' => __( 'Choose from Most Used Video Types', 'audiotheme' )
	);
	
	$args = array(
		'label' => __( 'Video Types', 'audiotheme' ),
		'labels' => $labels,
		'public' => true,
		'hierarchical' => true,
		'show_ui' => true,
		'show_in_nav_menus' => true,
		'args' => array( 'orderby' => 'term_order' ),
		'rewrite' => array( 'slug' => 'videos/type', 'with_front' => false ),
		'query_var' => true
	);
	
	register_taxonomy( 'video_type', 'video', $args );

}

/*-----------------------------------------------------------------------------------*/
/* Register "Video Tags" taxonomy for the Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add action to register "Video Tags" taxonomy for the Video Library CPT
add_action( 'init', 'audiotheme_register_video_tags' );

function audiotheme_register_video_tags() {

	$labels = array(
		'name' => __( 'Video Tags', 'audiotheme' ), 'taxonomy general name',
		'singular_name' => __( 'Video Tag', 'audiotheme' ), 'taxonomy singular name',
		'search_items' => __( 'Search Video Tags', 'audiotheme' ),
		'popular_items' => __( 'Popular Video Tags', 'audiotheme' ),
		'all_items' => __( 'All Video Tags', 'audiotheme' ),
		'edit_item' => __( 'Edit Video Tag', 'audiotheme' ),
		'update_item' => __( 'Update Video Tag', 'audiotheme' ),
		'add_new_item' => __( 'Add New Video Tag', 'audiotheme' ),
		'new_item_name' => __( 'New Video Tag', 'audiotheme' ),
		'separate_items_with_commas' => __( 'Separate Video Tags with commas', 'audiotheme' ),
		'add_or_remove_items' => __( 'Add or Remove Video Tags', 'audiotheme' ),
		'choose_from_most_used' => __( 'Choose from Most Used Video Tags', 'audiotheme' )
	);
	
	$args = array(
		'label' => __( 'Video Tags', 'audiotheme' ),
		'labels' => $labels,
		'public' => true,
		'hierarchical' => false,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud' => false,
		'args' => array( 'orderby' => 'term_order' ),
		'rewrite' => array( 'slug' => 'videos/tags', 'with_front' => false ),
		'query_var' => true
	);
	
	register_taxonomy( 'video_tag', 'video', $args );
	
}

/*-----------------------------------------------------------------------------------*/
/* Register Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add action to register Video Library CPT
add_action( 'init', 'audiotheme_register_videos' );

function audiotheme_register_videos() {

	$labels = array(
		'name' => __( 'Videos', 'audiotheme' ), 'post type general name',
		'singular_name' => __( 'Video', 'audiotheme' ), 'post type singular name',
		'add_new' => __( 'Add New Video', 'audiotheme' ), 'Videos',
		'add_new_item' => __( 'Add New Video', 'audiotheme' ),
		'edit' => __( 'Edit Video', 'audiotheme' ),
		'edit_item' => __( 'Edit Video', 'audiotheme' ),
		'new_item' => __( 'New Video', 'audiotheme' ),
		'view' => __( 'View Video', 'audiotheme' ),
		'view_item' => __( 'View Video', 'audiotheme' ),
		'search_items' => __( 'Search Videos', 'audiotheme' ),
		'not_found' => __( 'No Videos found', 'audiotheme' ),
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
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
    	'show_ui' => true, 
    	'show_in_menu' => true, 
    	'query_var' => true,
		'rewrite' => array( 'slug' => 'videos', 'with_front' => false ),
		'capability_type' => 'post',
		'hierarchical' => false,
    	'menu_position' => 20,
    	'supports' => $supports
	);
	
	register_post_type( 'video', $args );

}

/*-----------------------------------------------------------------------------------*/
/* Contextual update messages for Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add filter display contextual custom post type messages 
add_filter( 'post_updated_messages', 'audiotheme_video_updated_messages' );

function audiotheme_video_updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['videos'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __( 'Video updated. <a href="%s">View Video</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		2 => __( 'Custom field updated.', 'audiotheme' ),
		3 => __( 'Custom field deleted.', 'audiotheme' ),
		4 => __( 'Video updated.', 'audiotheme' ),
		/* translators: %s: date and time of the revision */
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Video restored to revision from %s', 'audiotheme' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Video published. <a href="%s">View Video</a>', 'audiotheme' ), esc_url( get_permalink( $post_ID ) ) ),
		7 => __( 'Video saved.', 'audiotheme' ),
		8 => sprintf( __( 'Video submitted. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		9 => sprintf( __( 'Video scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Video</a>', 'audiotheme' ),
		// translators: Publish box date format, see http://php.net/date
		date_i18n( __( 'M j, Y @ G:i', 'audiotheme' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
		10 => sprintf( __( 'Video draft updated. <a target="_blank" href="%s">Preview Video</a>', 'audiotheme' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
	);

	return $messages;
	
}

/*-----------------------------------------------------------------------------------*/
/* Add custom columns for Video Library CPT
/*-----------------------------------------------------------------------------------*/

// Add filter to display Video Library custom columns
add_filter( 'manage_edit-video_columns', 'audiotheme_custom_video_columns' );

function audiotheme_custom_video_columns( $video_columns ) {
	$video_columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => _x( __( 'Video', 'audiotheme' ), 'column name' ),
		'author' => __( 'Author', 'audiotheme' ),
		'video-type' => __( 'Video Type', 'audiotheme' ),
		'video-tags' => __( 'Video Tags', 'audiotheme' ),
		'date' => _x( __( 'Date', 'audiotheme' ), 'column name' )
	);
	return $video_columns;

}

/*-----------------------------------------------------------------------------------*/
/* Show "Video Type" in the Video Library CPT custom columns
/*-----------------------------------------------------------------------------------*/

// Add action to display "Video Type"
add_action( 'manage_posts_custom_column', 'audiotheme_video_taxonomy_column' );

function audiotheme_video_taxonomy_column( $video_columns ) {
	global $post;
	
	switch ( $video_columns ) {
		case 'video-type' :
			$taxonomy = 'video_type';
			$post_type = get_post_type( $post->ID );
			$video_types = get_the_terms( $post->ID, $taxonomy );
			if ( !empty( $video_types ) ) {
				foreach ( $video_types as $video_type )
				$post_terms[] = "<a href=\"edit.php?post_type={$post_type}&{$taxonomy}={$video_type->slug}\">" . esc_html( sanitize_term_field( 'name', $video_type->name, $video_type->term_id, $taxonomy, 'edit' ) ) . '</a>';
				echo join( ', ', $post_terms );
			} else echo __( '<i>No video types.</i>', 'audiotheme' );
			
		break;
	}
}

/*-----------------------------------------------------------------------------------*/
/* Show "Video Tags" in the Video Library CPT custom columns
/*-----------------------------------------------------------------------------------*/

// Add action to display "Video Tags"
add_action( 'manage_posts_custom_column', 'audiotheme_video_tag_column' );

function audiotheme_video_tag_column( $tag_column ) {
	global $post;
	
	switch ( $tag_column ) {
		case 'video-tags' :
			$taxonomy = 'video_tag';
			$post_type = get_post_type( $post->ID );
			$video_tags = get_the_terms( $post->ID, $taxonomy );
			if ( !empty( $video_tags ) ) {
				foreach ( $video_tags as $video_tag )
				$post_terms[] = "<a href=\"edit.php?post_type={$post_type}&{$taxonomy}={$video_tag->slug}\">" . esc_html( sanitize_term_field( 'name', $video_tag->name, $video_tag->term_id, $taxonomy, 'edit' ) ) . "</a>";
				echo join( ', ', $post_terms );
			} else echo __( '<i>No video tags.</i>', 'audiotheme' );
			break;
	}

}

/*-----------------------------------------------------------------------------------*/
/* Add a custom meta box to Video Library CPT editor screen
/*-----------------------------------------------------------------------------------*/

// Add the meta box
add_action( 'add_meta_boxes', 'audiotheme_add_video_meta' );

function audiotheme_add_video_meta() {
	add_meta_box( 'audiotheme-video-meta', __( 'Video Library: Add Video URL', 'audiotheme' ), 'audiotheme_video_meta_cb', 'video', 'normal', 'high' );
}

// Render the meta box
function audiotheme_video_meta_cb( $post ) {

	// Store the saved values
	$video = get_post_meta( $post->ID, '_video_url', true );

	// Nonce to verify intention later
	wp_nonce_field( 'save_audiotheme_video_meta', 'audiotheme_video_nonce' );
	
	// TOOD: move css and javascript to external files
	?>
	
	<p>
		<label for="audiotheme-video-url"><?php _e( 'Video URL', 'audiotheme' ); ?></label><br>
		<input type="text" name="_video_url" value="<?php echo esc_url( $video ); ?>" id="audiotheme-video-url" class="widefat">
	</p>
	<p>
		<a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank"><?php _e( 'View list of supported video services.', 'audiotheme' ); ?></a>
	</p>
	<p>
		<input type="button" id="button-get-video-data" class="button" value="Get Thumbnail" style="vertical-align: middle">
		<img src="<?php echo admin_url( 'images/wpspin_light.gif' ); ?>" alt="" id="get-video-data-indicator" class="ajax-indicator">
	</p>
	
	<style type="text/css">
	.ajax-indicator { display: none; margin: 0 0 0 5px; vertical-align: middle;}
	</style>
	<script>
	(function($) {
		$('#button-get-video-data').on('click', function(e) {
			var spinner = $('#get-video-data-indicator').show();
			e.preventDefault();
			
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					'action': 'audiotheme_get_video_data',
					'post_id': $('#post_ID').val(),
					'video_url': $('#audiotheme-video-url').val()
				},
				dataType: 'json',
				success: function(data) {
					spinner.hide();
					
					if ('undefined' != typeof data.thumbnail_id && '0' != data.thumbnail_meta_box_html) {
						WPSetThumbnailID(data.thumbnail_id);
						WPSetThumbnailHTML(data.thumbnail_meta_box_html);
					}
				}
			});
		});
	})(jQuery);
	</script>
	<?php
	// TODO: the thumbnail should be sufficient for a preview, but an error message if the video isn't embeddable would be helfpul
	// show video preview if an URL is set
	/*if ( $video ) {
		echo '<p>' . __( '<strong>Video Preview</strong>', 'audiotheme' ) . '</p>';
		echo wp_oembed_get( $video, array( 'width' => 570 ) );
	}*/
}

add_action( 'wp_ajax_audiotheme_get_video_data', 'audiotheme_get_video_data' );

function audiotheme_get_video_data() {
	global $post_ID;
	
	$post_ID = absint( $_POST['post_id'] );
	
	$json['post_id'] = $post_ID;
	
	add_filter( 'oembed_dataparse', 'audiotheme_oembed_dataparse', 1, 3 );
	$oembed = wp_oembed_get( $_POST['video_url'] );
	
	if ( $thumbnail_id = get_post_thumbnail_id( $post_ID ) ) {
		$json['thumbnail_id'] = $thumbnail_id;
		$json['thumbnail_meta_box_html'] = _wp_post_thumbnail_html( $thumbnail_id );
	}
	
	die( json_encode( $json ) );
}

// Copy the video thumbnail
function audiotheme_oembed_dataparse( $return, $data, $url ) {
	global $post_ID;
	
	// support for any oEmbed providers that respond with thumbnail_url
	if ( isset( $data->thumbnail_url ) ) {
		$current_source = get_post_meta( $post_ID, '_thumbnail_source', true );
		$current_source_id = get_post_meta( $post_ID, '_thumbnail_source_id', true );
		
		if ( ! get_post_thumbnail_id( $post_ID ) && $data->thumbnail_url == $current_source ) {
			// reuse the existing source data instead of making another copy of the thumbnail
			set_post_thumbnail( $post_ID, $current_source_id );
		} elseif ( ! get_post_thumbnail_id( $post_ID ) || $data->thumbnail_url != $current_source ) {
			// add new thumbnail if the returned URL doesn't match the current source URL or if there isn't a current thumbnail
			add_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			media_sideload_image( $data->thumbnail_url, $post_ID );
			remove_action( 'add_attachment', 'audiotheme_add_video_thumbnail' );
			
			if ( $thumbnail_id = get_post_thumbnail_id( $post_ID ) ) {
				// store source so we don't copy the same image on repeated requests
				update_post_meta( $post_ID, '_thumbnail_source', $data->thumbnail_url, true );
				update_post_meta( $post_ID, '_thumbnail_source_id', $thumbnail_id, true );
			}
		}
	}
	
	return $return;
}

// Set attachment as post thumbnail
function audiotheme_add_video_thumbnail( $attachment_id ) {
	global $post_ID;
	set_post_thumbnail( $post_ID, $attachment_id );
}

// Save meta box
add_action( 'save_post', 'audiotheme_video_save' );

function audiotheme_video_save( $id ) {

	// Let's not auto save the data
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 

	// Check our nonce
	if( !isset( $_POST['audiotheme_video_nonce'] ) || !wp_verify_nonce( $_POST['audiotheme_video_nonce'], 'save_audiotheme_video_meta' ) ) return;

	// Make sure the current user can edit the post
	if( !current_user_can( 'edit_post' ) ) return;

	// Make sure we get a clean url here with esc_url
	if( isset( $_POST['_video_url'] ) )
		update_post_meta( $id, '_video_url', esc_url( $_POST['_video_url'], array( 'http' ) ) );
}

?>