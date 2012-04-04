<?php
/**
 * Theme Options
 *
 */
global $theme_options;

/* Setup Menu Item */
$theme_options = new Struts_Options( 'soundcheck_options', 'soundcheck_options', 'Theme Options' );

/* Setup Sections */
$theme_options->add_section( 'logo_section', __( 'Logo and Background', 'soundcheck' ) );
$theme_options->add_section( 'hero_section', __( 'Hero Slider', 'soundcheck' ) );
$theme_options->add_section( 'featured_section', __( 'Featured Image Carousel', 'soundcheck' ) );
$theme_options->add_section( 'audio_section', __( 'Audio Player', 'soundcheck' ) );
$theme_options->add_section( 'products_section', __( 'Products Setup', 'soundcheck' ) );
$theme_options->add_section( 'social_section', __( 'Social Media', 'soundcheck' ) );
$theme_options->add_section( 'footer_section', __( 'Footer', 'soundcheck' ) );

/* Setup Common Variables */
$categories = soundcheck_get_category_list();


/* Setup Options */
// LOGO
$theme_options->add_option( 'logo_url', 'image', 'logo_section' )
    ->label( __( 'Logo URL:', 'soundcheck' ) )
    ->description( __( 'Your image can be any width and height.', 'soundcheck' ) );
    
$theme_options->add_option( 'text_logo_desc', 'checkbox', 'logo_section' )
    ->label( __( 'Enable Site Tagline', 'soundcheck' ) )
    ->description( __( 'Display your site tagline beneath your text/image based logo.', 'soundcheck' ) );

  
// Hero Slider Section
$default_hero_slide = get_cat_ID( 'hero-slides' );
$theme_options->add_option( 'hero_category', 'select', 'hero_section' )
    ->label( __( 'Category', 'soundcheck' ) )
    ->description( __( 'Select a category to be used for the Hero slides.', 'soundcheck' ) )
    ->default_value( $default_hero_slide )
    ->valid_values( $categories );
    
$theme_options->add_option( 'hero_randomize', 'checkbox', 'hero_section' )
    ->label( __( 'Randomize Slides', 'soundcheck' ) )
    ->description( __( 'Yes, display slides in random order.', 'soundcheck' ) );

$theme_options->add_option( 'hero_fx', 'select', 'hero_section' )
    ->label( __( 'Slide Animation', 'soundcheck' ) )
    ->description( __( 'Choose a type of animation for each slide transition.', 'soundcheck' ) )
    ->default_value( 'scrollVert' )
    ->valid_values( array(
        'scrollVert' => __( 'Slide (vertical)', 'soundcheck' ),
        'fade' => __( 'Fade', 'soundcheck' ) 
    ));

$theme_options->add_option( 'hero_speed', 'text', 'hero_section' )
    ->label( __( 'Hero Slider Speed', 'soundcheck' ) )
    ->description( __( 'Speed (in seconds) at which the slides will animate between transitions.', 'soundcheck' ) )
    ->default_value( 1 );

$theme_options->add_option( 'hero_timeout', 'text', 'hero_section' )
    ->label( __( 'Hero Slider Timeout', 'soundcheck' ) )
    ->description( __( 'Time (in seconds) before transitioning to the next slide. Leave empty to disable.', 'soundcheck' ) )
    ->default_value( 6 );  


// Featured Image Carousel Section
$theme_options->add_option( 'image_carousel_home', 'checkbox', 'featured_section' )
    ->label( __( 'Image Carousel Display', 'soundcheck' ) )
    ->description( __( 'Display Image Carousel On Home Page?', 'soundcheck' ) );

$theme_options->add_option( 'image_carousel_category', 'select', 'featured_section' )
    ->label( __( 'Image Carousel Category', 'soundcheck' ) )
    ->description( __( 'Select which category should be shown in the image carousel. By default, all categories will be used.', 'soundcheck' ) )
    ->valid_values( $categories );
    
    
// Audio Section
$theme_options->add_option( 'audio_single_playlist', 'checkbox', 'audio_section' )
    ->label( __( 'Display playlist by default?', 'soundcheck' ) )
    ->description( __( 'Display playlist by default on single audio post pages.', 'soundcheck' ) );

$theme_options->add_option( 'audio_single_autoplay', 'checkbox', 'audio_section' )
    ->label( __( 'Autoplay audio?', 'soundcheck' ) )
    ->description( __( 'Autoplay audio by default on single audio post pages.', 'soundcheck' ) );
    
// Products Secton
$theme_options->add_option( 'products_category', 'select', 'products_section' )
    ->label( __( 'Products Category', 'soundcheck' ) )
    ->description( __( 'Select which category should be used for the Products display.', 'soundcheck' ) )
    ->valid_values( $categories );


// Social Media Section
$theme_options->add_option( 'social_rss', 'checkbox', 'social_section' )
    ->label( __( 'RSS', 'soundcheck' ) )
    ->description( __( 'Display RSS Icon', 'soundcheck' ) )
	->default_value( 1 );

$theme_options->add_option( 'feedburner_url', 'text', 'social_section' )
    ->label( __( 'FeedBurner', 'soundcheck' ) )
    ->description( __( 'Provide your <a href="http://feedburner.google.com" title="Go to FeedBurner" target="_blank">FeedBurner</a> feed name to enable this functionality. The RSS icon must be enabled above.', 'soundcheck' ) )
    ->default_value( 6 );    

// Social Media Section
$social_media = array(
	'amazon' => 'Amazon',
	'bandcamp' => 'Bandcamp',
	'facebook' => 'Facebook',
	'flickr' => 'Flickr',
	'itunes' => 'iTunes',
	'lastfm' => 'Last.fm',
	'myspace' => 'MySpace',
	'soundcloud' => 'SoundCloud',
	'twitter' => 'Twitter',
	'vimeo' => 'Vimeo',
	'youtube' => 'YouTube'
);

foreach( $social_media as $key => $value ) :
	$theme_options->add_option( "hero_$key", 'text', 'social_section' )
    ->label( sprintf( __( '%s', 'soundcheck' ), $value ) )
    ->description( __( 'Provide URL including http://', 'soundcheck' ) );
endforeach;


// Footer
$theme_options->add_option( 'footer_copyright', 'textarea', 'footer_section' )
    ->label( __( 'Footer Text', 'soundcheck' ) )
    ->description( __( 'Set the text to be displayed in the footer.', 'soundcheck' ) );


/* Initialize Options */
$theme_options->initialize();

?>