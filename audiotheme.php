<?php
/*
AudioTheme Framework
The engine of AudioTheme 

Version: 1.0.0
Author: AudioTheme
Author URI: http://AudioTheme.com
License: GPLv2



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

/**
 * Define Constants
 *
 * @since 1.0.0
 */
define( 'AUDIOTHEME_VERSION', '1.0.0' );

if ( ! defined( 'AUDIOTHEME_DIR' ) )
    define( 'AUDIOTHEME_DIR', get_template_directory() . '/audiotheme/' );
if ( ! defined( 'AUDIOTHEME_URI' ) )
    define( 'AUDIOTHEME_URI', get_template_directory_uri() . '/audiotheme/' );


/**
 * General Includes
 *
 * @since 1.0.0
 */
require( AUDIOTHEME_DIR . 'includes/general-template.php' );
require( AUDIOTHEME_DIR . 'includes/functions.php' );
require( AUDIOTHEME_DIR . 'includes/load-p2p.php' );
require( AUDIOTHEME_DIR . 'includes/media.php' );
require( AUDIOTHEME_DIR . 'widgets/widgets.php' );


/**
 * AudioTheme Setup
 *
 * @since 1.0.0
 */
add_action( 'after_setup_theme', 'audiotheme_setup' );
function audiotheme_setup() {
	require( AUDIOTHEME_DIR . 'includes/default-filters.php' );
	
	/* Include Admin functionality */
	if ( is_admin() ) {
		require( AUDIOTHEME_DIR . 'admin/admin.php' );
	}
	
	/* Include Gigs CPT functionality */
	require( AUDIOTHEME_DIR . 'discography/discography.php' );
	require( AUDIOTHEME_DIR . 'galleries/galleries.php' );
	require( AUDIOTHEME_DIR . 'gigs/gigs.php' );
	require( AUDIOTHEME_DIR . 'videos/videos.php' );
	
	add_action( 'init', 'audiotheme_init' );
	add_action( 'init', 'audiotheme_register_scripts' );
	add_action( 'widgets_init', 'audiotheme_widgets_init' );
}


/**
 * AudioTheme Init
 *
 * @since 1.0.0
 */
function audiotheme_init() {
	
}

/**
 * Register Scripts
 *
 * @since 1.0.0
 */
function audiotheme_register_scripts() {
	wp_register_script( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/js/audiotheme-admin.js', array( 'jquery-ui-sortable' ) );
	
	// Related: http://core.trac.wordpress.org/ticket/18909
	wp_register_style( 'jquery-ui-theme-smoothness', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/smoothness/jquery-ui.css' );
	wp_register_style( 'jquery-ui-theme-audiotheme', AUDIOTHEME_URI . 'admin/css/jquery-ui-audiotheme.css', array( 'jquery-ui-theme-smoothness' ) );
	
	wp_register_style( 'audiotheme-admin', AUDIOTHEME_URI . 'admin/css/audiotheme-admin.css' );
}
?>