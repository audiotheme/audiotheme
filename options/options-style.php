<?php  
/**
 * Theme Styles
 *
 */
global $theme_styles;

$theme_styles = new Struts_Options( 'audiotheme_styles', 'audiotheme_styles', 'Theme Styles' );

/* Sections */
$theme_styles->add_section( 'enable_section', __( '* Color Palette *', 'audiotheme' ) );
$theme_styles->add_section( 'primary_section', __( 'Primary Colors', 'audiotheme' ) );
$theme_styles->add_section( 'text_section', __( 'Text Colors', 'audiotheme' ) );
$theme_styles->add_section( 'css_section', __( 'Custom CSS', 'audiotheme' ) );


/* Enable Styles */
$theme_styles->add_option( 'color_palette', 'select', 'enable_section' )
    ->label( __( 'Color Palette', 'audiotheme' ) )
    ->description( __( 'Choose a color palette.', 'audiotheme' ) )
    ->default_value( 'default' )
    ->valid_values( array(
        'default'   => __( 'Default', 'audiotheme' ),
    	'custom' => __( 'Custom', 'audiotheme' )
    ) );
    

/* Primary Color Section */
$theme_styles->add_option( 'primary_1', 'color', 'primary_section' )
    ->label( __( 'Primary &mdash; Color 1', 'audiotheme' ) )
    ->description( __( 'For best results, this is lightest color of the primary colors.', 'audiotheme' ) )
    ->default_value( '#bec4cc' );

$theme_styles->add_option( 'primary_2', 'color', 'primary_section' )
    ->label( __( 'Primary &mdash; Color 2', 'audiotheme' ) )
    ->description( __( 'For best results, this is the second lightest color.', 'audiotheme' ) )
    ->default_value( '#3F434A' );
    
$theme_styles->add_option( 'primary_3', 'color', 'primary_section' )
    ->label( __( 'Primary &mdash; Color 3', 'audiotheme' ) )
    ->description( __( 'For best results, this is the second darkest color.', 'audiotheme' ) )
    ->default_value( '#252A31' );
    
$theme_styles->add_option( 'primary_4', 'color', 'primary_section' )
    ->label( __( 'Primary &mdash; Color 4', 'audiotheme' ) )
    ->description( __( 'For best results, this is darkest color of the primary colors.', 'audiotheme' ) )
    ->default_value( '#191D22' );


/* Text Color Section */
$theme_styles->add_option( 'text_site_info', 'color', 'text_section' )
    ->label( __( 'Logo Color', 'audiotheme' ) )
    ->description( __( 'Text color of a text based logo and description.', 'audiotheme' ) )
    ->default_value( '#ffffff' );
        
$theme_styles->add_option( 'text_primary', 'color', 'text_section' )
    ->label( __( 'Link &mdash; Color 1', 'audiotheme' ) )
    ->description( __( 'Link color by default', 'audiotheme' ) )
    ->default_value( '#ffffff' );

$theme_styles->add_option( 'text_shadows', 'checkbox', 'text_section' )
    ->label( __( 'Text Shadows', 'audiotheme' ) )
    ->description( __( 'Remove text shadows. If you would like to remove the text shadows, check this box.', 'audiotheme' ) );


/* Custom CSS Section */
$theme_styles->add_option( 'custom_css_code', 'textarea', 'css_section' )
    ->label( __( 'Quick CSS Code', 'audiotheme' ) );
    
    
$theme_styles->initialize();
?>