<?php
/**
 * LESS compiler with Theme Customizer support.
 *
 * LESS should only recompile when it detects file changes, so changing the
 * variables won't have any effect on the front-end. However, that also means
 * changes can't be previewed. The workaround is to load a Theme Customizer
 * style sheet separately *after* the main style sheet has already been loaded
 * so that it won't be recompiled to prevent changes from being made live prematurely.
 *
 * @package AudioTheme
 * @since 1.0.0
 * @deprecated 1.9.0
 */

/**
 * Load the LESS compiler and set up Theme Customizer support.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_less_setup() {
	if ( $support = get_theme_support( 'audiotheme-less' ) ) {
		if ( ! class_exists( 'lessc' ) ) {
			require( AUDIOTHEME_DIR . 'includes/vendor/lessphp/lessc.inc.php' );
		}

		require( AUDIOTHEME_DIR . 'includes/vendor/wp-less/wp-less.php' );
		wp_less::instance();

		add_action( 'wp_loaded', 'audiotheme_less_register_vars', 20 );
		add_filter( 'wp_less_cache_url', 'audiotheme_less_force_ssl' );

		// Register a style sheet specifically for the Theme Customizer.
		$stylesheet = ( empty( $support[0]['customize_stylesheet'] ) ) ? '' : $support[0]['customize_stylesheet'];
		if ( ! empty( $stylesheet ) ) {
			wp_register_style( 'audiotheme-less-customize', $stylesheet );
			add_action( 'wp_footer', 'audiotheme_less_customize_enqueue_stylesheet' );
		}
	}
}

/**
 * Force SSL on LESS cache URLs.
 *
 * @since 1.3.1
 * @deprecated 1.9.0
 *
 * @param string $url URL to compiled CSS.
 * @return string
 */
function audiotheme_less_force_ssl( $url ) {
	if ( is_ssl() ) {
		$url = set_url_scheme( $url, 'https' );
	}

	return $url;
}

/**
 * Execute the callback function to register LESS vars and fire an action so
 * additional vars can be registered.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_less_register_vars() {
	$support = get_theme_support( 'audiotheme-less' );
	$callback = ( empty( $support[0]['less_vars_callback'] ) ) ? '' : $support[0]['less_vars_callback'];

	// Always points to the parent theme.
	add_less_var( 'templateurl', '~"' . get_template_directory_uri() . '/"' );

	if ( ! empty( $callback ) && function_exists( $callback ) ) {
		call_user_func( $callback );
	}

	do_action( 'audiotheme_less_register_vars' );
}

/**
 * Enqueue the Theme Customizer style sheet.
 *
 * This should only be run after the main style sheets have been output in
 * order to prevent changes from being made live prematurely.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_less_customize_enqueue_stylesheet() {
	global $wp_customize;

	// Load a separate customizer stylesheet when the customizer is being used.
	// Should prevent temporary changes from displaying on the front-end.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		return;
	}

	// Enqueue the Theme Customizer style sheet if it has been registered.
	if ( wp_style_is( 'audiotheme-less-customize', 'registered' ) ) {
		add_filter( 'less_force_compile', '__return_true' );
		wp_enqueue_style( 'audiotheme-less-customize' );
	}
}
