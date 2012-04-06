# Getting Started

Place the 'audiotheme' folder inside of your theme folder and then place the following code in your functions.php

```php
<?php
require_once(TEMPLATEPATH . '/audiotheme/init.php');
```

# Theme Options
AudioTheme Framework uses a customized version of the [Struts Framework](https://github.com/thethemefoundry/struts).

To add support for the AudioTheme's theme options section, add the following to your theme's functions.php

```php
<?php function themename_setup() {
    add_theme_support('audiotheme-options');
}
add_action( 'after_setup_theme', 'themename_setup' );
```

## Default Options

AudioTheme Framework comes with some default options that are typically the same throughout all themes.

Current default options include:

- Logo (logo_url)
  
To add support for default AudioTheme options, add the following to your theme's functions.php:

```php
<?php function themename_setup() {
    add_theme_support('audiotheme-options');
    add_theme_support('audiotheme-default-options');
}
add_action( 'after_setup_theme', 'themename_setup' );
```

## Custom Theme Options

To add your own options to the theme options section, add the following to your theme's functions.php to get started:

```php
<?php function my_theme_options(){
    global $audiotheme_options;
    
    // New Section
    $audiotheme_options->add_section( 'styles_section', __( 'Styles', 'audiotheme' ) );
    
    // New Option
    $audiotheme_options->add_option( 'primary_color', 'color', 'styles_section' )
        ->label( __( 'Primary Color', 'audiotheme' ) )
        ->description( __( 'This is lightest color of the primary colors.', 'audiotheme' ) )
        ->default_value( '#bec4cc' );
}
add_action( 'audiotheme_custom_options', 'my_theme_options' );
```

## Retrieving options

To retrieve a theme option, you can use `audiotheme_get_option` and pass in the option name and optionally a default value if the option is empty. Example below:

```php
<?php audiotheme_get_option( 'logo_url', 'http://example.com/images/default_logo.png' ); ?>
```

If the option is empty and a default isn't provided, it will return `false`.