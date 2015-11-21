<?php
/**
 * Hooks for modifying WordPress behavior.
 *
 * @package AudioTheme
 * @since 1.0.0
 */

/**
 * Set up AudioTheme templates when they're loaded.
 *
 * Limits default scripts and styles to load only for AudioTheme templates.
 *
 * @since 1.2.0
 *
 * @param string $template Template path.
 */
function audiotheme_template_setup( $template ) {
	if ( is_audiotheme_default_template( $template ) ) {
		add_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_scripts' );
	}
}

/**
 * Enqueue default frontend scripts and styles.
 *
 * Themes can remove default styles and scripts by removing this hook:
 * <code>remove_action( 'wp_enqueue_scripts', 'audiotheme_enqueue_scripts' );</code>
 *
 * @since 1.2.0
 */
function audiotheme_enqueue_scripts() {
	wp_enqueue_script( 'audiotheme' );
	wp_enqueue_style( 'audiotheme' );
}

/**
 * Add wrapper open tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_before_main_content() {
	echo '<div class="audiotheme">';
}

/**
 * Add wrapper close tags in default templates for theme compatibility.
 *
 * @since 1.2.0
 */
function audiotheme_after_main_content() {
	echo '</div>';
}
