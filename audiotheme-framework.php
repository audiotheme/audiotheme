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
if ( !defined( 'ABSPATH' ) ) exit;

define( 'AUDIOTHEME_DIR', plugin_dir_url(__FILE__) );

// Include the additional files (custom post types, widgets, etc)
audiotheme_includes();

add_action('init',  'audiotheme_init');
function audiotheme_init(){

	load_plugin_textdomain( 'audiotheme', FALSE, AUDIOTHEME_DIR.'languages' );
	
	if( current_user_can( 'manage_options' ) ){
		// Display admin pages
		//add_action( 'admin_menu', 'audiotheme_create_menu' );
	}
		 	
}

function audiotheme_includes(){
    // Custom post types
    include_once( 'custom-post-types/video.php' );
    include_once( 'options/options-setup.php' );
}

add_action( 'after_setup_theme', 'audiotheme_options_init', 30 );

?>