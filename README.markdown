# Theme Options

To add support for default AudioTheme options, add the following to your theme's functions.php:

```php
function themename_setup() {
    add_theme_support('audiotheme-default-options');
}
add_action( 'after_setup_theme', 'themename_setup' );
```