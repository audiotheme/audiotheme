<?php
/**
 * Yoast SEO compatibility hooks provider.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.4.0
 */

/**
 * Yoast SEO compatibility hooks provider class.
 *
 * @package AudioTheme
 * @since   2.4.0
 */
class AudioTheme_Integration_YoastSEO extends AudioTheme_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 2.4.0
	 */
	public function register_hooks() {
		// An alternative approach that would allow the meta box on AudioTheme
		// Archive edit screens to function as expected would be to use the
		// 'wpseo_frontend_page_type_simple_page_id' filter. It would also allow
		// OpenGraph meta tags to be managed. This approach is more compatible
		// with existing sites, so has been used instead.

		add_filter( 'wpseo_accessible_post_types', array( $this, 'filter_accessible_post_types' ) );
		add_action( 'load-seo_page_wpseo_titles',  array( $this, 'disable_audiotheme_archive_settings' ) );
	}

	/**
	 * Filter accessible post types.
	 *
	 * Removing the 'audiotheme_archive' post type from the list of accessible
	 * post types disables the Yoast SEO meta box when editing an AudioTheme
	 * archive.
	 *
	 * @since 2.4.0
	 *
	 * @param  array $post_types Array with post type names as keys.
	 * @return array
	 */
	public function filter_accessible_post_types( $post_types ) {
		unset( $post_types['audiotheme_archive'] );
		return $post_types;
	}

	/**
	 * Disable settings for AudioTheme archives on the Yoast SEO settings screen.
	 *
	 * Settings for the 'audiotheme_archive' CPT aren't actually used by default
	 * since Yoast pulls the archive settings from each post type. This is a
	 * hacky method for disabling the panel to help prevent confusion.
	 *
	 * @since 2.4.0
	 */
	public function disable_audiotheme_archive_settings() {
		$post_type_object         = get_post_type_object( 'audiotheme_archive' );
		$post_type_object->public = false;
	}
}
