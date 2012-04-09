<?php
include AUDIOTHEME_DIR . 'admin/functions.php';
include AUDIOTHEME_DIR . 'admin/meta-boxes.php';
include AUDIOTHEME_DIR . 'admin/options.php';

AudioTheme_Options::setup();

add_action( 'init', 'audiotheme_admin_setup' );

function audiotheme_admin_setup() {
	add_action( 'admin_enqueue_scripts', 'audiotheme_enqueue_admin_scripts' );
	add_action( 'add_meta_boxes', 'audiotheme_add_meta_boxes' );
	add_filter( 'user_contactmethods', 'audiotheme_edit_user_contact_info' );
	
	if ( current_theme_supports( 'audiotheme-options' ) ) {
		add_action( 'admin_menu', 'audiotheme_options_init', 9 );
	}
}

function audiotheme_options_init() {
	$options = AudioTheme_Options::get_instance();
	$panel = $options->add_panel( 'theme-options', __( 'Theme Options', 'audiotheme' ), array(
		'menu_title' => __( 'Theme Options', 'audiotheme' ),
		'option_group' => 'audiotheme_options',
		'option_name' => array( 'audiotheme_options' ),
		'show_in_menu' => 'themes.php'
	) );
}

function audiotheme_enqueue_admin_scripts() {
	// Should be loaded on every admin request
	wp_enqueue_style( 'audiotheme-admin' );
}

/**
 * Add Metabox
 *
 * @since 1.0
 */
function audiotheme_add_meta_boxes() {
	add_meta_box( 'audiotheme-record-meta', __( 'Record Details', 'audiotheme' ), 'audiotheme_record_meta_cb', 'audiotheme_record', 'normal',  'high' );
	add_meta_box( 'audiotheme-track-meta', __( 'Track Details', 'audiotheme' ), 'audiotheme_track_meta_cb', 'audiotheme_track', 'normal', 'high' );
	add_meta_box( 'audiotheme-video-meta', __( 'Video Library: Add Video URL', 'audiotheme' ), 'audiotheme_video_meta_cb', 'audiotheme_video', 'side', 'high' );
}


function audiotheme_edit_user_contact_info( $contactmethods ) {
	/* Remove contact options */
	unset( $contactmethods['aim'] );
	unset( $contactmethods['yim'] );
	unset( $contactmethods['jabber'] );
	
	/* Add Contact Options */
	$contactmethods['twitter'] = __( 'Twitter <span class="description">(username)</span>', 'audiotheme' );
	$contactmethods['facebook'] = __( 'Facebook  <span class="description">(link)</span>', 'audiotheme' );
	
	return $contactmethods;
}
?>