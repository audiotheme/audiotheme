<?php
/**
 * AudioTheme Theme Option
 *
 * Function called to get a Theme Option. The returned value defaults to false
 * unless otherwise set.
 *
 * @since 1.0.0
 * 
 * @param string The option key
 * @param mixed Optional. Default value to return if option key doesn't exist.
 * @param string Optional. Retrieve a non-standard option.
 * @return mixed The option value or $default or false.
 */
function get_audiotheme_theme_option( $key, $default = false, $option_name = '' ) {
	$option_name = ( empty( $option_name ) ) ? 'audiotheme_options' : $option_name;
	
	$options = get_option( $option_name );
	
	return ( isset( $options[ $key ] ) ) ? $options[ $key ] : $default;
}
?>