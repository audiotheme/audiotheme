<?php
add_action( 'init', 'audiotheme_load_galleries_admin' );

function audiotheme_load_galleries_admin() {
	add_action( 'admin_init', 'audiotheme_gallery_media_iframe_load' );
	add_filter( 'post_updated_messages', 'audiotheme_gallery_post_updated_messages' );
	add_filter( 'nav_menu_items_audiotheme_archive_pages', 'audiotheme_gallery_archive_menu_item' );
}

function audiotheme_gallery_post_updated_messages( $messages ) {
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

	return $messages;
}

function audiotheme_edit_gallery_meta_boxes( $post ) {
	add_action( 'edit_form_advanced', 'audiotheme_gallery_media_iframe' );
}

function audiotheme_gallery_media_iframe() {
	global $post;
	
	$iframe_src = add_query_arg( array(
		'post_id' => $post->ID,
		'tab' => 'gallery',
		'context' => 'embed'
	), admin_url( 'media-upload.php' ) );
	echo '<div id="gallery-ui">';
		printf( '<iframe id="audiotheme-gallery-iframe" src="%1$s" width="100%%" height="360" frameborder="0" border="0"></iframe>', esc_url( $iframe_src ) );
	echo '</div>';
	?>
	<script>
	var win = window.dialogArguments || opener || parent || top,
		galleryFrame = jQuery('#audiotheme-gallery-iframe').attr('scrolling', 'no');
	
	jQuery('#gallery-ui').insertBefore('#postdivrich');
	
	win.audiotheme_resize_gallery_iframe = function( height ) {
		galleryFrame.height( height );
	}
	</script>
	<style type="text/css">
	#gallery-ui { margin-bottom: 15px;}
	#gallery-ui iframe { }
	</style>
	<?php
}

function audiotheme_gallery_media_iframe_load() {
    global $pagenow;
	
	// TODO: some of these actions need to be attached after an image is uploaded, too
	if ( isset( $_REQUEST['context'] ) && 'embed' == $_REQUEST['context'] && ! empty( $_REQUEST['post_id'] ) && 'media-upload.php' == $pagenow ) {
		if ( 'audiotheme_gallery' == get_post_type( $_REQUEST['post_id'] ) ) {
			add_filter( 'media_upload_tabs', 'audiotheme_gallery_media_upload_tabs', 9 );
			add_action( 'admin_head-media-upload-popup', 'audiotheme_gallery_media_styles' );
			add_filter( 'media_upload_form_url', 'audiotheme_gallery_medial_upload_form_url' );
			add_filter( 'attachment_fields_to_edit', 'audiotheme_gallery_media_item_fields', 20, 2 );
			add_filter( 'get_media_item_args', 'audiotheme_gallery_media_item_args' );
		}
	}
}

function audiotheme_gallery_media_upload_tabs( $tabs ) {
	$tabs = array(
		'gallery' => __( 'Gallery', 'audiotheme-i18n' ),
		'type' => __( 'Upload', 'audiotheme-i18n' )
	);
	
	return $tabs;
}

function audiotheme_gallery_media_styles() {
	?>
	<style type="text/css">
	body { min-width: 300px;}
	#media-items,
	#media-items .media-item,
	#media-upload .widefat { width: 100%;
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;}
	div#media-upload-header { background: #fff;}
	#sort-buttons { max-width: 100%;}
	#sidemenu a { margin-right: 6px; padding-left: 6px; padding-right: 6px; color: #aaa; background: #fbfbfb; border: 1px solid #e6e6e6;
		-moz-border-top-right-radius: 3px; -moz-border-top-left-radius: 3px;
		-webkit-border-top-right-radius: 3px; -webkit-border-top-left-radius: 3px;
		border-top-right-radius: 3px; border-top-left-radius: 3px;}
	#sidemenu a:hover { color: #d54e21;}
	
	.media-item .describe input[type="text"],
	.media-item .describe textarea { width: 100%;}
	.media-upload-form { margin-left: 0; margin-right: 0;}
	</style>
	<script>
	jQuery(function($) {
		var win = window.dialogArguments || opener || parent || top,
			mediaForm = $('.media-upload-form'),
			lastHeight = document.body.scrollHeight;
		
		$('#gallery-settings').hide();
		
		setInterval( function() {
			var currentHeight = mediaForm.offset().top + mediaForm.height();
			if (currentHeight != lastHeight) {
				win.audiotheme_resize_gallery_iframe( currentHeight );
				lastHeight = currentHeight;
			}
			
		}, 100 );
	});
	</script>
	<?php
}

function audiotheme_gallery_medial_upload_form_url( $url ) {
	return add_query_arg( 'context', 'embed', $url );
}

function audiotheme_gallery_media_item_fields( $form_fields, $post ) {
	$allowed_keys = array_flip( array( 'post_title', 'image_alt', 'post_excerpt', 'post_content', 'menu_order' ) );
	$form_fields = array_intersect_key( $form_fields, $allowed_keys );
	
	return $form_fields;
}

function audiotheme_gallery_media_item_args( $args ) {
	$args['send'] = false;
	
	return $args;
}

function audiotheme_gallery_archive_menu_item( $posts ) {
	global $_nav_menu_placeholder;
	$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval( $_nav_menu_placeholder ) - 1 : -1;
	
	$permalink = get_option( 'permalink_structure' );
	if ( ! empty( $permalink ) ) {
		$url = home_url( '/gallery/' );
	} else {
		$url = add_query_arg( 'post_type', 'audiotheme_gallery', home_url( '/' ) );
	}
	
	array_unshift( $posts, (object) array(
		'_add_to_top' => false,
		'ID' => 0,
		'object_id' => $_nav_menu_placeholder,
		'post_content' => '',
		'post_excerpt' => '',
		'post_parent' => '',
		'post_title' => _x( 'Gallery', 'nav menu archive label' ),
		'post_type' => 'nav_menu_item',
		'type' => 'custom',
		'url' => $url
	) );
	
	return $posts;
}
?>