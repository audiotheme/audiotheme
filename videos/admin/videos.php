<?php
add_action( 'init', 'audiotheme_load_videos_admin' );

function audiotheme_load_videos_admin() {
	add_action( 'add_meta_boxes', 'audiotheme_video_meta_boxes' );
	add_filter( 'post_updated_messages', 'audiotheme_video_post_updated_messages' );
	add_filter( 'manage_edit-audiotheme_video_columns', 'audiotheme_video_columns' );
	add_action( 'manage_posts_custom_column', 'audiotheme_video_display_column', 10, 2 );
	add_filter( 'nav_menu_items_audiotheme_archive_pages', 'audiotheme_video_archive_menu_item' );
}

function audiotheme_video_post_updated_messages( $messages ) {
	global $post, $post_ID;
	
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
 * Display Custom Video Columns
 *
 * @since 1.0
 */
function audiotheme_video_display_column( $column_name, $post_id ) {
	switch ( $column_name ) {
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

/**
 * Add Video Meta Boxes
 *
 * @since 1.0
 */
function audiotheme_video_meta_boxes() {
	add_meta_box( 'audiotheme-video-meta', __( 'Video Library: Add Video URL', 'audiotheme-i18n' ), 'audiotheme_video_meta_cb', 'audiotheme_video', 'side', 'high' );
}

/**
 * Video Metabox Callback
 *
 * @since 1.0
 *
 * @TODO Move css and javascript to external files
 * @TODO The thumbnail should be sufficient for a preview, 
 *       but an error message if the video isn't embeddable 
 *       would be helfpul, else show video preview if URL is set.
 *
 */
function audiotheme_video_meta_cb( $post ) {

	// Store the saved values
	$video = get_audiotheme_post_video_url( $post->ID );

	// Nonce to verify intention later
	wp_nonce_field( 'save_audiotheme_video_meta', 'audiotheme_video_nonce' );

	?>
	<p>
		<?php _e( 'Enter a video URL from one of the WordPress', 'audiotheme-i18n' ) ?> <a href="http://codex.wordpress.org/Embeds#Okay.2C_So_What_Sites_Can_I_Embed_From.3F" target="_blank"><?php _e( 'supported video services.', 'audiotheme-i18n' ); ?></a>
	</p>
	
	<p>
		<input type="text" name="_video_url" value="<?php echo esc_url( $video ); ?>" id="audiotheme-video-url" class="widefat" placeholder="<?php _e( 'Video URL', 'audiotheme-i18n' ); ?>">
	</p>
	
	<div id="audiotheme-video-preview">
		<?php if( $video ) echo get_the_audiotheme_post_video( $post->ID, array( 'width' => 258 ) ); ?>
	</div>
	
	<p>
		<input type="button" id="button-get-video-data" class="button" value="<?php _e( 'Get Thumbnail', 'audiotheme-i18n' ) ?>" style="vertical-align: middle">
		<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" id="get-video-data-indicator" class="ajax-indicator">
	</p>
	
	<style type="text/css">
	#audiotheme-video-preview iframe { background: url(<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ) ?>) center center no-repeat;}
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
					
					if('undefined' != typeof data.thumbnail_id && '0' != data.thumbnail_meta_box_html) {
						WPSetThumbnailID(data.thumbnail_id);
						WPSetThumbnailHTML(data.thumbnail_meta_box_html);
					}
				}
			});
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * Get Video Data
 *
 * @since 1.0
 */
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

/**
 * Parse Video oEmbed Data
 *
 * @since 1.0
 */
function audiotheme_oembed_dataparse( $return, $data, $url ) {
	global $post_ID;
	
	// Support for any oEmbed providers that respond with thumbnail_url
	if( isset( $data->thumbnail_url ) ) {
		
		$current_source = get_post_meta( $post_ID, '_thumbnail_source', true );
		$current_source_id = get_post_meta( $post_ID, '_thumbnail_source_id', true );
		
		if ( ! get_post_thumbnail_id( $post_ID ) && $data->thumbnail_url == $current_source ) {
			
			// Re-use the existing source data instead of making another copy of the thumbnail
			set_post_thumbnail( $post_ID, $current_source_id );
		
		} elseif ( ! get_post_thumbnail_id( $post_ID ) || $data->thumbnail_url != $current_source ) {
			
			// Add new thumbnail if the returned URL doesn't match the 
			// current source URL or if there isn't a current thumbnail
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

/**
 * Add Video Thumbnail
 *
 * @since 1.0
 */
function audiotheme_add_video_thumbnail( $attachment_id ) {
	global $post_ID;
	set_post_thumbnail( $post_ID, $attachment_id );
}

/**
 * Save Video Metabox Values
 *
 * @since 1.0
 */
function audiotheme_video_save( $id ) {
	// Let's not auto save the data
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return; 

	// Check our nonce
	if( ! isset( $_POST['audiotheme_video_nonce'] ) || ! wp_verify_nonce( $_POST['audiotheme_video_nonce'], 'save_audiotheme_video_meta' ) )
		return;

	// Make sure the current user can edit the post
	if( !current_user_can( 'edit_post' ) )
		return;

	// Make sure we get a clean url here with esc_url
	if( isset( $_POST['_video_url'] ) )
		update_post_meta( $id, '_video_url', esc_url( $_POST['_video_url'], array( 'http', 'https' ) ) );
}

function audiotheme_video_archive_menu_item( $posts ) {
	global $_nav_menu_placeholder;
	$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval( $_nav_menu_placeholder ) - 1 : -1;
	
	$permalink = get_option( 'permalink_structure' );
	if ( ! empty( $permalink ) ) {
		$url = home_url( '/videos/' );
	} else {
		$url = add_query_arg( 'post_type', 'audiotheme_video', home_url( '/' ) );
	}
	
	array_unshift( $posts, (object) array(
		'_add_to_top' => false,
		'ID' => 0,
		'object_id' => $_nav_menu_placeholder,
		'post_content' => '',
		'post_excerpt' => '',
		'post_parent' => '',
		'post_title' => _x( 'Videos', 'nav menu archive label' ),
		'post_type' => 'nav_menu_item',
		'type' => 'custom',
		'url' => $url
	) );
	
	return $posts;
}
?>