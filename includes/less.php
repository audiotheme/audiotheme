<?php
/**
 * LESS compiler with Theme Customizer support.
 *
 * LESS should only recompile when it detects file changes, so changing the
 * variables won't have any effect on the front-end. However, that also means
 * changes can't be previewed. The workaround is to load a Theme Customizer
 * style sheet separately *after* the main style sheet has already been loaded
 * so that it won't be recompiled to prevent changes from being made live prematurely.
 */

/**
 * Load the LESS compiler and set up Theme Customizer support.
 *
 * @since 1.0.0
 */
function audiotheme_less_setup() {
	if ( $support = get_theme_support( 'audiotheme-less' ) ) {
		require( AUDIOTHEME_DIR . 'includes/lib/wp-less/wp-less.php' );
		wp_less::instance();

		add_action( 'wp_loaded', 'audiotheme_less_register_vars', 20 );

		// Register a style sheet specifically for the Theme Customizer.
		$stylesheet = ( empty( $support[0]['customize_stylesheet'] ) ) ? '' : $support[0]['customize_stylesheet'];
		if ( ! empty( $stylesheet ) ) {
			wp_register_style( 'audiotheme-less-customize', $stylesheet );

			add_action( 'wp_loaded', 'audiotheme_less_customize_manage_stylesheets', 50 );
			add_action( 'customize_save', 'audiotheme_less_recompile_stylesheets' );
		}
	}
}

/**
 * Execute the callback function to register LESS vars and fire an action so
 * additional vars can be registered.
 *
 * @since 1.0.0
 */
function audiotheme_less_register_vars() {
	$support = get_theme_support( 'audiotheme-less' );
	$callback = ( empty( $support[0]['less_vars_callback'] ) ) ? '' : $support[0]['less_vars_callback'];
	
	// Always points to the parent theme.
	add_less_var( 'templateurl', '~"' . get_template_directory_uri() . '"' );

	if ( ! empty( $callback ) && function_exists( $callback ) ) {
		call_user_func( $callback );
	}

	do_action( 'audiotheme_less_register_vars' );
}

/**
 * Determine if the main LESS style sheet or the Theme Customizer style sheet
 * should be recompiled.
 *
 * @since 1.0.0
 */
function audiotheme_less_customize_manage_stylesheets() {
	global $wp_customize;

	// Force LESS files to recompile on the request after updates are saved via the Theme Customizer.
	if ( ! is_admin() && ( ! $wp_customize || ! $wp_customize->is_preview() ) && get_option( 'audiotheme_less_recompile_stylesheets' ) ) {
		add_filter( 'less_force_compile', '__return_true' );
		delete_option( 'audiotheme_less_recompile_stylesheets' );
	}

	// Load a separate customizer stylesheet when the customizer is being used.
	// Should prevent temporary changes from displaying on the front-end.
	if ( $wp_customize && $wp_customize->is_preview() ) {
		add_action( 'wp_footer', 'audiotheme_less_customize_enqueue_stylesheet' );
	}
}

/**
 * Enqueue the Theme Customizer style sheet.
 *
 * This should only be run after the main style sheets have been output in
 * order to prevent changes from being made live prematurely.
 *
 * @since 1.0.0
 */
function audiotheme_less_customize_enqueue_stylesheet() {
	add_filter( 'less_force_compile', '__return_true' );

	// Enqueue the Theme Customizer style sheet if it has been registered.
	if ( wp_style_is( 'audiotheme-less-customize', 'registered' ) ) {
		wp_enqueue_style( 'audiotheme-less-customize' );
	}
}

/**
 * Set a flag to recompile the main style sheets when the Theme Customizer
 * changes are saved.
 *
 * @since 1.0.0
 */
function audiotheme_less_recompile_stylesheets() {
	update_option( 'audiotheme_less_recompile_stylesheets', 1 );
}
