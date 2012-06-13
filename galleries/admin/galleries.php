<?php
add_action( 'init', 'audiotheme_load_galleries_admin' );

function audiotheme_load_galleries_admin() {
	add_filter( 'post_updated_messages', 'audiotheme_gallery_post_updated_messages' );
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
	add_action( 'edit_form_advanced', 'audiotheme_edit_gallery_iframe' );
}

function audiotheme_edit_gallery_iframe() {
	global $post;
	
	$iframe_src = add_query_arg( 'post_id', $post->ID, admin_url( 'media-upload.php' ) );
	echo '<div id="gallery-ui">';
		printf( '<iframe src="%1$s" width="100%%" height="360"></iframe>', esc_url( $iframe_src ) );
	echo '</div>';
	?>
	<script>
	jQuery('#gallery-ui').insertBefore('#postdivrich');
	</script>
	<style type="text/css">
	#gallery-ui { margin-bottom: 15px;}
	#gallery-ui iframe { }
	</style>
	<?php
}
?>