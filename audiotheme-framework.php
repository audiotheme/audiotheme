<?php
/*
Plugin Name: AudioTheme Framework
Plugin URI: http://wordpress.org/extend/plugins/audiotheme-framework
Description: The engine of AudioTheme 
Version: 1.0.0
Author: AudioTheme
Author URI: http://AudioTheme.com
License: GPLv2

Text Domain: audiotheme
Domain Path: /languages/

Copyright 2012 AudioTheme

This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; either version 2 of the License, or (at 
your option) any later version.This program is distributed in the hope 
that it will be useful, but WITHOUT ANY WARRANTY; without even the 
implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

define( 'AUIDIOTHEME_VERSION', 1.0 );
define( 'AUDIOTHEME_DIR', plugin_dir_url( __FILE__ ) );


// Include the additional files (custom post types, widgets, etc)
audiotheme_includes();


add_action( 'init',  'audiotheme_init' );
/**
 * AudioTheme Init
 *
 * @since 1.0
 */
function audiotheme_init() {
	load_plugin_textdomain( 'audiotheme', false, AUDIOTHEME_DIR . 'languages' );
	
	add_action( 'admin_menu',   'audiotheme_change_post_menu_label' );
	add_action( 'admin_menu',   'audiotheme_remove_admin_menus' );
	add_action( 'widgets_init', 'audiotheme_unregister_default_widgets', 1 );
	add_action( 'init',         'audiotheme_change_post_object_label' );
}


/**
 * AudioTheme Includes
 *
 * @since 1.0
 */
function audiotheme_includes() {

	/* Admin */
	include_once( 'admin/user-meta.php' );
	
	/* Custom Post Types */
	include_once( 'custom-post-types/track.php' );
	include_once( 'custom-post-types/records.php' );
	include_once( 'custom-post-types/video.php' );
	include_once( 'custom-post-types/gallery.php' );
	
	/* Functions */
	include_once( 'functions/feed.php' );
	include_once( 'functions/formatting.php' );
	include_once( 'functions/general.php' );
	include_once( 'functions/image.php' );
	    
	/* Metaboxes */
	include_once( 'metaboxes/video.php' );
	    
	/* Options */
	include_once( 'options/options-setup.php' );
	
	/* Metaboxes */
	include_once( 'shortcodes/footer.php' );
	
	/* Tools */
	include_once( 'tools/custom-field-redirect.php' );
	include_once( 'tools/post-templates.php' );
	
}



/**
 * Unregister Default WP Widgets
 *
 * @since 1.0
 */
function audiotheme_unregister_default_widgets() {
	
	unregister_widget('WP_Widget_Calendar');
	unregister_widget('WP_Widget_Links');
	unregister_widget('WP_Widget_Meta');
	unregister_widget('WP_Widget_Tag_Cloud');
	unregister_widget('WP_Widget_RSS');
	unregister_widget('WP_Widget_Akismet');

}


/**
 * Remove Admin Menus
 *
 * @since 1.0
 */
function audiotheme_remove_admin_menus() {
     
     remove_menu_page( 'link-manager.php' );

}


/**
 * Change Post Menu Labels
 *
 * @since 1.0
 */
function audiotheme_change_post_menu_label() {
	global $menu, $submenu;
	
	$menu[5][0] = 'News';
	$submenu['edit.php'][5][0] = 'News';
	$submenu['edit.php'][10][0] = 'Add News';
	$submenu['edit.php'][16][0] = 'News Tags';
	
	echo '';
}


/**
 * Change Post Object Labels
 *
 * @since 1.0
 */
function audiotheme_change_post_object_label() {
	global $wp_post_types;
	
	$labels = &$wp_post_types['post']->labels;
	
	$labels->name               = 'News';
	$labels->singular_name      = 'News';
	$labels->add_new            = 'Add News';
	$labels->add_new_item       = 'Add News';
	$labels->edit_item          = 'Edit News';
	$labels->new_item           = 'News';
	$labels->view_item          = 'View News';
	$labels->search_items       = 'Search News';
	$labels->not_found          = 'No News found';
	$labels->not_found_in_trash = 'No News found in Trash';
} 


?>