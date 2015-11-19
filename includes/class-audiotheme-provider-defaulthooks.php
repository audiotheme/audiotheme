<?php
/**
 * General hooks provider.
 *
 * @package AudioTheme
 * @since 1.9.0
 */

/**
 * General hooks provider class.
 *
 * @package AudioTheme
 * @since 1.9.0
 */
class AudioTheme_Provider_GeneralHooks {
	/**
	 * Plugin instance.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Plugin_AudioTheme
	 */
	protected $plugin;

	/**
	 * Set a reference to a plugin instance.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Plugin $plugin Main plugin instance.
	 * @return $this
	 */
	public function set_plugin( AudioTheme_Plugin $plugin ) {
		$this->plugin = $plugin;
		return $this;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_filter( 'wp_image_editors',             array( $this, 'register_image_editors' ) );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_audio_attachment_for_js' ), 10, 3 );
	}

	/**
	 * Register custom image editors.
	 *
	 * @since 1.9.0
	 *
	 * @param array $editors Array of image editors.
	 * @return array
	 */
	public function register_image_editors( $editors ) {
		include_once( AUDIOTHEME_DIR . 'includes/class-audiotheme-image-editor-gd.php' );
		include_once( AUDIOTHEME_DIR . 'includes/class-audiotheme-image-editor-imagick.php' );
		include_once( AUDIOTHEME_DIR . 'includes/class-audiotheme-image-pixel-gd.php' );

		array_unshift( $editors, 'AudioTheme_Image_Editor_GD' );
		array_unshift( $editors, 'AudioTheme_Image_Editor_Imagick' );

		return $editors;
	}

	/**
	 * Add audio metadata to attachment response objects.
	 *
	 * @since 1.9.0
	 *
	 * @param array   $response Attachment data to send as JSON.
	 * @param WP_Post $attachment Attachment object.
	 * @param array   $meta Attachment meta.
	 * @return array
	 */
	public function prepare_audio_attachment_for_js( $response, $attachment, $meta ) {
		if ( 'audio' !== $response['type'] ) {
			return $response;
		}

		$response['audiotheme'] = $meta;

		return $response;
	}
}
