<?php
/**
 * Discography module.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */

/**
 * Discography module class.
 *
 * @package AudioTheme\Discography
 * @since 1.9.0
 */
class AudioTheme_Module_Discography extends AudioTheme_Module {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $admin_menu_id = 'toplevel_page_edit-post_type-audiotheme_record';

	/**
	 * Whether the module is a core module.
	 *
	 * @since 1.9.0
	 * @var bool
	 */
	protected $is_core_module = true;

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 */
	public function __construct( $args = array() ) {
		$this->set_name( __( 'Discography', 'audiotheme' ) );
		$this->set_description( __( 'Upload album artwork, assign titles and tracks, add audio files, and enter links to purchase your music.', 'audiotheme' ) );
	}
}
