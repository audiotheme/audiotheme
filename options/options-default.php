<?php
/**
 * Theme Options
 *
 */
global $theme_options;

/* Setup Menu Item */
$theme_options = new Struts_Options( 'audiotheme_options', 'audiotheme_options', 'Theme Options' );

/* Setup Sections */
$theme_options->add_section( 'logo_section', __( 'Logo', 'audiotheme' ) );

/* Setup Options */

$theme_options->add_option( 'logo_url', 'image', 'logo_section' )
    ->label( __( 'Logo URL:', 'audiotheme' ) )
    ->description( __( 'Your image can be any width and height.', 'audiotheme' ) );
    
/* Initialize Options */
$theme_options->initialize();

?>