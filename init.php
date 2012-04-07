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

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

define( 'AUDIOTHEME_VERSION', 1.0 );
define( 'AUDIOTHEME_DIR', get_template_directory() . '/audiotheme/' );
define( 'AUDIOTHEME_URI', get_template_directory_uri() . '/audiotheme/' );


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

}


/**
 * AudioTheme Includes
 *
 * @since 1.0
 */
function audiotheme_includes() {

	/* Admin */
	include_once( 'admin/menus.php' );
	include_once( 'admin/user-meta.php' );
	
	/* Post Types */
	include_once( 'post-types/gallery.php' );
	include_once( 'post-types/records.php' );
	include_once( 'post-types/track.php' );
	include_once( 'post-types/video.php' );
	
	/* Functions */
	include_once( 'functions/feed.php' );
	include_once( 'functions/formatting.php' );
	include_once( 'functions/general.php' );
	include_once( 'functions/image.php' );
	    
	/* Metaboxes */
    include_once( 'metaboxes/track.php' );
	include_once( 'metaboxes/video.php' );
	    
	/* Options */
	include_once( 'options/options-setup.php' );
	
	/* Metaboxes */
	include_once( 'shortcodes/footer.php' );
	
	/* Tools */
	include_once( 'tools/custom-field-redirect.php' );
	include_once( 'tools/post-templates.php' );
	
}


?>