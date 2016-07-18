<?php
/**
 * Main plugin functionality.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Main plugin class.
 *
 * @package AudioTheme
 * @since   2.0.0
 */
class AudioTheme_Plugin extends AudioTheme_AbstractPlugin {
	/**
	 * License.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_License
	 */
	protected $license;

	/**
	 * Modules.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_Module_Collection
	 */
	protected $modules;

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->license = new AudioTheme_License();
		$this->modules = new AudioTheme_ModuleCollection();
	}

	/**
	 * Magic get method.
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'license' :
				return $this->license;
			case 'modules' :
				return $this->modules;
		}
	}

	/**
	 * Load the plugin.
	 *
	 * @since 2.0.0
	 */
	public function load() {
		scb_init( array( $this, 'load_p2p_core' ) );
		$this->load_modules();
	}

	/**
	 * Load Posts 2 Posts core.
	 *
	 * Posts 2 Posts requires two custom database tables to store post
	 * relationships and relationship metadata. If an alternative version of the
	 * library doesn't exist, the tables are created on admin_init.
	 *
	 * @since 1.0.0
	 */
	public function load_p2p_core() {
		if ( ! defined( 'P2P_TEXTDOMAIN' ) ) {
			define( 'P2P_TEXTDOMAIN', 'audiotheme' );
		}

		if ( ! function_exists( 'p2p_register_connection_type' ) ) {
			require( $this->get_path( 'vendor/scribu/lib-posts-to-posts/autoload.php' ) );
		}

		P2P_Storage::init();
		P2P_Query_Post::init();

		add_action( 'admin_init', array( $this, 'maybe_install_p2p_tables' ) );
	}

	/**
	 * Install P2P database tables.
	 *
	 * @since 2.0.0
	 */
	public function maybe_install_p2p_tables() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_version = (int) get_option( 'p2p_storage' );
		if ( P2P_Storage::$version === $current_version ) {
			return;
		}

		P2P_Storage::install();
		update_option( 'p2p_storage', P2P_Storage::$version );
	}

	/**
	 * Load the active modules.
	 *
	 * Modules are always loaded when viewing the AudioTheme Settings screen so
	 * they can be toggled with instant access.
	 *
	 * @since 2.0.0
	 */
	protected function load_modules() {
		foreach ( $this->modules as $module ) {
			// Load all modules on the Dashboard screen.
			if ( ! $this->is_dashboard_screen() && ! $module->is_active() ) {
				continue;
			}

			$this->register_hooks( $module->load() );
		}
	}

	/**
	 * Whether the current request is the dashboard screen.
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	protected function is_dashboard_screen() {
		return is_admin() && isset( $_GET['page'] ) && 'audiotheme' == $_GET['page'];
	}
}
