# Getting Started

Add the '/audiotheme/' directory inside your theme and then place the following code in your functions.php

```php
<?php
require_once( TEMPLATEPATH . '/audiotheme/audiotheme.php' );
```

# Theme Options
To add support for the AudioTheme's theme options section, add the following to your theme's functions.php

```php
<?php function themename_setup() {
    add_theme_support( 'audiotheme-options' );
}
add_action( 'after_setup_theme', 'themename_setup' );
```

## Custom Theme Options

To add your own options to the theme options section, add the following to your theme's functions.php to get started:

```php
<?php function my_theme_options(){
   $options = AudioTheme_Options::get_instance();
	
	$panel = $options->set_panel( 'theme-options' );
	
	$section = $options->add_section( '_default', '' );
		$options->add_field( 'thickbox_image', 'logo', __( 'Logo', 'audiotheme' ), $section, array( 'description' => '<br>(300x150 max size)' ) );
	
	$tab = $options->add_tab( 'scripts', 'Scripts' );
		$section = $options->add_section( '_default_scripts_section', '', $tab );
			$options->add_field( 'textarea', 'header_scripts', __( 'Header', 'audiotheme' ), $section );
	
	$tab = $options->add_tab( 'styles', __( 'Styles', 'audiotheme' ) );
		$section = $options->add_section( '_default_styles_section', '', $tab );
			$options->add_field( 'text', 'primary_color', __( 'Primary Color', 'audiotheme' ), $section, array( 'default_value' => '#bec4cc' ) );
}
add_action( 'admin_init', 'my_theme_options' );
```

## Retrieving options

To retrieve a theme option, you can use `get_audiotheme_option` and pass in the option name and optionally a default value in case the option is empty. Example below:

```php
<?php get_audiotheme_option( 'logo_url', 'http://example.com/images/default_logo.png' ); ?>
```

If the option is empty and a default isn't provided, it will return `false`.