<?php
/**
 * AudioTheme Theme Option
 *
 * Function called to get a Theme Option. 
 * The option defaults to false unless otherwise set.
 *
 * @since 1.0.0
 */
function get_audiotheme_theme_option( $key, $default = false, $option_name = '' ) {
	$option_name = ( empty( $option_name ) ) ? 'audiotheme_options' : $option_name;
	
	$options = get_option( $option_name );
	
	return ( isset( $options[ $key ] ) ) ? $options[ $key ] : $default;
}
?>