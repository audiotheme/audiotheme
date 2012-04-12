<?php
/**
 * Admin Inclusions
 *
 * @since 1.0
 */
include AUDIOTHEME_DIR . 'admin/functions.php';
include AUDIOTHEME_DIR . 'admin/meta-boxes.php';
include AUDIOTHEME_DIR . 'admin/options.php';
include AUDIOTHEME_DIR . 'admin/post-type-screens.php';


/**
 * Theme Options setup
 *
 * @since 1.0
 */
AudioTheme_Options::setup();


/**
 * Admin Setup
 *
 * @since 1.0
 */
add_action( 'init', 'audiotheme_admin_setup' );

function audiotheme_admin_setup() {
	add_action( 'save_post', 'audiotheme_record_save' );
	add_action( 'save_post', 'audiotheme_track_save' );
	add_action( 'save_post', 'audiotheme_video_save' );
	add_action( 'wp_ajax_audiotheme_get_video_data', 'audiotheme_get_video_data' );
	
	add_action( 'admin_enqueue_scripts', 'audiotheme_enqueue_admin_scripts' );
	add_action( 'add_meta_boxes', 'audiotheme_meta_boxes' );
	add_filter( 'user_contactmethods', 'audiotheme_edit_user_contact_info' );
	
	add_filter( 'post_updated_messages', 'audiotheme_post_updated_messages' );
	add_filter( 'manage_edit-audiotheme_record_columns', 'audiotheme_record_columns' );
	add_filter( 'manage_edit-audiotheme_video_columns', 'audiotheme_video_columns' );
	add_action( 'manage_posts_custom_column', 'audiotheme_display_custom_column', 10, 2 );
	
	if ( current_theme_supports( 'audiotheme-options' ) ) {
		add_action( 'admin_menu', 'audiotheme_options_init', 9 );
	}
}


/**
 * Theme Options Init
 *
 * @since 1.0
 */
function audiotheme_options_init() {
	$options = AudioTheme_Options::get_instance();
	$panel = $options->add_panel( 'theme-options', __( 'Theme Options', 'audiotheme' ), array(
		'menu_title' => __( 'Theme Options', 'audiotheme' ),
		'option_group' => 'audiotheme_options',
		'option_name' => array( 'audiotheme_options' ),
		'show_in_menu' => 'themes.php'
	) );
}


/**
 * Enqueue Admin Scripts
 *
 * Should be loaded on every admin request
 *
 * @since 1.0
 */
function audiotheme_enqueue_admin_scripts() {
	wp_enqueue_style( 'audiotheme-admin' );
}


/**
 * Add Meta Boxes
 *
 * @since 1.0
 */
function audiotheme_meta_boxes() {
	add_meta_box( 'audiotheme-record-meta', __( 'Record Details', 'audiotheme' ), 'audiotheme_record_meta_cb', 'audiotheme_record', 'normal',  'high' );
	add_meta_box( 'audiotheme-track-meta', __( 'Track Details', 'audiotheme' ), 'audiotheme_track_meta_cb', 'audiotheme_track', 'normal', 'high' );
	add_meta_box( 'audiotheme-video-meta', __( 'Video Library: Add Video URL', 'audiotheme' ), 'audiotheme_video_meta_cb', 'audiotheme_video', 'side', 'high' );
}


/**
 * Enqueue Admin Scripts
 *
 * @since 1.0
 */
function audiotheme_edit_user_contact_info( $contactmethods ) {
	// Remove contact options
	unset( $contactmethods['aim'] );
	unset( $contactmethods['yim'] );
	unset( $contactmethods['jabber'] );
	
	// Add Contact Options
	$contactmethods['twitter'] = __( 'Twitter <span class="description">(username)</span>', 'audiotheme' );
	$contactmethods['facebook'] = __( 'Facebook  <span class="description">(link)</span>', 'audiotheme' );
	
	return $contactmethods;
}
?>