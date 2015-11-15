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
	protected $admin_menu_id = 'menu-posts-audiotheme_gig';

	/**
	 * Whether the module is a core module.
	 *
	 * @since 1.9.0
	 * @var bool
	 */
	protected $is_core_module = true;

	/**
	 * Whether the module should show on the dashboard.
	 *
	 * @since 1.9.0
	 * @var bool
	 */
	protected $show_in_dashboard = true;

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		$this->set_name( __( 'Gigs & Venues', 'audiotheme' ) );
		$this->set_description( __( 'Share event details with your fans, including location, venue, date, time, and ticket prices.', 'audiotheme' ) );
	}

	/**
	 * Register module hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {

	}
}
