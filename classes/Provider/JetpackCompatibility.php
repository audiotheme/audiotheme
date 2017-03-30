<?php
/**
 * Jetpack compatibility hooks provider.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.4
 */

/**
 * Jetpack compatibility hooks provider class.
 *
 * @package AudioTheme
 * @since   2.0.4
 */
class AudioTheme_Provider_JetpackCompatibility extends AudioTheme_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.4
	 */
	public function register_hooks() {
		add_filter( 'option_jetpack_content_featured_images_archive', array( $this, 'filter_featured_image_content_option' ) );
		add_filter( 'option_jetpack_content_featured_images_post', array( $this, 'filter_featured_image_content_option' ) );
	}

	/**
	 * Filter featured image content options.
	 *
	 * Ensures featured images for AudioTheme post types show regardless of the
	 * values set in the Content Options module.
	 *
	 * @since 2.0.4
	 *
	 * @param  boolean $value Default value.
	 * @return boolean
	 */
	public function filter_featured_image_content_option( $value ) {
		$post_types = array( 'audiotheme_record', 'audiotheme_track', 'audiotheme_video' );

		if ( in_array( get_post_type(), $post_types, true ) ) {
			$value = true;
		}

		return $value;
	}
}
