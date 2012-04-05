<?php
/**
 * Theme Options
 *
 */
global $audiotheme_options;

/* Setup Menu Item */
$audiotheme_options = new Struts_Options( 'audiotheme_options', 'audiotheme_options', 'Theme Options' );

if( current_theme_supports( 'audiotheme-default-options' ) ){
    /* Setup Sections */
    $audiotheme_options->add_section( 'logo_section', __( 'Logo', 'audiotheme' ) );
    
    /* Setup Options */
    
    $audiotheme_options->add_option( 'logo_url', 'image', 'logo_section' )
        ->label( __( 'Logo URL:', 'audiotheme' ) )
        ->description( __( 'Your image can be any width and height.', 'audiotheme' ) );
}

// Call any option functions defined in the current theme
do_action('audiotheme_custom_options');

/* Initialize Options */
$audiotheme_options->initialize();

?>