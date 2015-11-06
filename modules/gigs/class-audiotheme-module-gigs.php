<?php
/**
 * Gigs module.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */

/**
 * Gigs module class.
 *
 * @package AudioTheme\Gigs
 * @since 1.9.0
 */
class AudioTheme_Module_Gigs extends AudioTheme_Module {
	/**
	 * Admin menu item HTML id.
	 *
	 * Used for hiding menu items when toggling modules.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $admin_menu_id = 'toplevel_page_audiotheme-gigs';

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
		$this->set_name( __( 'Gigs & Venues', 'audiotheme' ) );
		$this->set_description( __( 'Share event details with your fans, include: location, venue, date, time, and ticket prices.', 'audiotheme' ) );
	}
}