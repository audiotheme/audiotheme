<?php
/*
AudioTheme Framework
The engine of AudioTheme 

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

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Define Constants
 *
 * @since 1.0
 */
define( 'AUDIOTHEME_VERSION', 1.0 );
define( 'AUDIOTHEME_DIR', get_template_directory() . '/audiotheme/' );
define( 'AUDIOTHEME_URI', get_template_directory_uri() . '/audiotheme/' );


/**
 * General Inclusions
 *
 * @since 1.0
 */
include AUDIOTHEME_DIR . 'includes/general-template.php';
include AUDIOTHEME_DIR . 'includes/functions.php';
include AUDIOTHEME_DIR . 'includes/formatting.php';
include AUDIOTHEME_DIR . 'includes/media.php';


/**
 * AudioTheme Setup
 *
 * @since 1.0
 */
add_action( 'after_setup_theme', 'audiotheme_setup' );
function audiotheme_setup() {
	/* Include Shortcodes */
	include AUDIOTHEME_DIR . 'includes/default-filters.php';
	include AUDIOTHEME_DIR . 'includes/shortcodes.php';
	
	/* Include Admin functionality */
	if ( is_admin() ) {
		include AUDIOTHEME_DIR . 'admin/admin.php';
	}
	
	/* Include Gigs CPT functionality */
	include AUDIOTHEME_DIR . 'gigs/gigs.php';
	
	add_action( 'init', 'audiotheme_init' );
	add_action( 'init', 'audiotheme_register_scripts' );
}


/**
 * AudioTheme Init
 *
 * @since 1.0
 */
function audiotheme_init() {

	register_post_type( 'audiotheme_gallery', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Galleries', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Gallery', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'gallery', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Gallery', 'audiotheme' ),
			'edit_item'          => __( 'Edit Gallery', 'audiotheme' ),
			'new_item'           => __( 'New Gallery', 'audiotheme' ),
			'view_item'          => __( 'View Gallery', 'audiotheme' ),
			'search_items'       => __( 'Search Galleries', 'audiotheme' ),
			'not_found'          => __( 'No galleries found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No galleries found in Trash', 'audiotheme' ),
			'all_items'          => __( 'All Galleries', 'audiotheme' )
		),
		'menu_position'          => 9,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'galleries', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' )
	) );
	
	register_post_type( 'audiotheme_record', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Records', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Record', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'record', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Record', 'audiotheme' ),
			'edit_item'          => __( 'Edit Record', 'audiotheme' ),
			'new_item'           => __( 'New Record', 'audiotheme' ),
			'view_item'          => __( 'View Record', 'audiotheme' ),
			'search_items'       => __( 'Search Records', 'audiotheme' ),
			'not_found'          => __( 'No records found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No records found in Trash', 'audiotheme' ),
			'all_items'          => __( 'Records', 'audiotheme' )
		),
		'menu_position'          => 7,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'records', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
		'taxonomies'             => array( 'post_tag' )
	) );
	
	register_post_type( 'audiotheme_track', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Tracks', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Track', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'track', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Track', 'audiotheme' ),
			'edit_item'          => __( 'Edit Track', 'audiotheme' ),
			'new_item'           => __( 'New Track', 'audiotheme' ),
			'view_item'          => __( 'View Track', 'audiotheme' ),
			'search_items'       => __( 'Search Tracks', 'audiotheme' ),
			'not_found'          => __( 'No tracks found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No tracks found in Trash', 'audiotheme' ),
			'all_items'          => __( 'Tracks', 'audiotheme' )
		),
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'records', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => 'edit.php?post_type=audiotheme_record',
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' )
	) );
	
	register_post_type( 'audiotheme_video', array(
		'capability_type'        => 'post',
		'has_archive'            => false,
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Videos', 'post type general name', 'audiotheme' ),
			'singular_name'      => _x( 'Video', 'post type singular name', 'audiotheme' ),
			'add_new'            => _x( 'Add New', 'video', 'audiotheme' ),
			'add_new_item'       => __( 'Add New Video', 'audiotheme' ),
			'edit_item'          => __( 'Edit Video', 'audiotheme' ),
			'new_item'           => __( 'New Video', 'audiotheme' ),
			'view_item'          => __( 'View Video', 'audiotheme' ),
			'search_items'       => __( 'Search Videos', 'audiotheme' ),
			'not_found'          => __( 'No videos found', 'audiotheme' ),
			'not_found_in_trash' => __( 'No videos found in Trash', 'audiotheme' ),
			'all_items'          => __( 'Videos', 'audiotheme' )
		),
		'menu_position'          => 8,
		'public'                 => true,
		'publicly_queryable'     => true,
		'rewrite'                => array( 'slug' => 'videos', 'with_front' => false ),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ),
		'taxonomies'             => array( 'post_tag' )
	) );
	
	
	register_taxonomy( 'audiotheme_record_type', 'audiotheme_record', array(
		'args'                           => array( 'orderby' => 'term_order' ),
		'hierarchical'                   => true,
		'labels'                         => array(
			'name'                       => _x( 'Record Types', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => _x( 'Record Type', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => __( 'Search Record Types', 'audiotheme' ),
			'popular_items'              => __( 'Popular Record Types', 'audiotheme' ),
			'all_items'                  => __( 'All Record Types', 'audiotheme' ),
			'parent_item'                => __( 'Parent Record Type', 'audiotheme' ),
			'parent_item_colon'          => __( 'Parent Record Type:', 'audiotheme' ),
			'edit_item'                  => __( 'Edit Record Type', 'audiotheme' ),
			'view_item'                  => __( 'View Record Type', 'audiotheme' ),
			'update_item'                => __( 'Update Record Type', 'audiotheme' ),
			'add_new_item'               => __( 'Add New Record Type', 'audiotheme' ),
			'new_item_name'              => __( 'New Record Type Name', 'audiotheme' ),
			'separate_items_with_commas' => __( 'Separate record types with commas', 'audiotheme' ),
			'add_or_remove_items'        => __( 'Add or remove record types', 'audiotheme' ),
			'choose_from_most_used'      => __( 'Choose from most used record types', 'audiotheme' )
		),
		'public'                         => true,
		'query_var'                      => true,
		'rewrite'                        => array( 'slug' => 'records/type', 'with_front' => false ),
		'show_ui'                        => true,
		'show_in_nav_menus'              => true
	) );
	
	register_taxonomy( 'audiotheme_video_type', 'audiotheme_video', array(
		'args'                           => array( 'orderby' => 'term_order' ),
		'hierarchical'                   => true,
		'labels'                         => array(
			'name'                       => _x( 'Video Types', 'taxonomy general name', 'audiotheme' ),
			'singular_name'              => _x( 'Video Type', 'taxonomy singular name', 'audiotheme' ),
			'search_items'               => __( 'Search Video Types', 'audiotheme' ),
			'popular_items'              => __( 'Popular Video Types', 'audiotheme' ),
			'all_items'                  => __( 'All Video Types', 'audiotheme' ),
			'parent_item'                => __( 'Parent Video Type', 'audiotheme' ),
			'parent_item_colon'          => __( 'Parent Video Type:', 'audiotheme' ),
			'edit_item'                  => __( 'Edit Video Type', 'audiotheme' ),
			'view_item'                  => __( 'View Video Type', 'audiotheme' ),
			'update_item'                => __( 'Update Video Type', 'audiotheme' ),
			'add_new_item'               => __( 'Add New Video Type', 'audiotheme' ),
			'new_item_name'              => __( 'New Video Type Name', 'audiotheme' ),
			'separate_items_with_commas' => __( 'Separate video types with commas', 'audiotheme' ),
			'add_or_remove_items'        => __( 'Add or remove video types', 'audiotheme' ),
			'choose_from_most_used'      => __( 'Choose from most used video types', 'audiotheme' )
		),
		'public'                         => true,
		'query_var'                      => true,
		'rewrite'                        => array( 'slug' => 'records/type', 'with_front' => false ),
		'show_ui'                        => true,
		'show_in_nav_menus'              => true
	) );
	
}

/**
 * Register Scripts
 *
 * @since 1.0
 */
function audiotheme_register_scripts() {
	// Related: http://core.trac.wordpress.org/ticket/18909
	wp_register_style( 'jquery-ui-theme-smoothness', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'includes/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );
	
	wp_register_style( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/css/audiotheme-admin.css' );
}
?>