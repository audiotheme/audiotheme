<?php
/**
 * Main plugin functionality.
 *
 * @package AudioTheme
 * @since   1.9.0
 */

/**
 * Main plugin class.
 *
 * @package AudioTheme
 * @since   1.9.0
 */
class AudioTheme_Plugin_AudioTheme extends AudioTheme_Plugin {
	/**
	 * License.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_License
	 */
	protected $license;

	/**
	 * Modules.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Collection
	 */
	protected $modules;

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		$this->license = new AudioTheme_License();
		$this->modules = new AudioTheme_Module_Collection();
	}

	/**
	 * Load the plugin.
	 *
	 * @since 1.9.0
	 */
	public function load() {
		$this->load_modules();
	}

	/**
	 * Retrieve the license.
	 *
	 * @since 1.9.0
	 *
	 * @return AudioTheme_License
	 */
	public function get_license() {
		return $this->license;
	}

	/**
	 * Retrieve the modules collection.
	 *
	 * @since 1.9.0
	 *
	 * @return AudioTheme_Module_Collection
	 */
	public function get_modules() {
		return $this->modules;
	}

	/**
	 * Load the active modules.
	 *
	 * Modules are always loaded when viewing the AudioTheme Settings screen so
	 * they can be toggled with instant access.
	 *
	 * @since 1.9.0
	 */
	protected function load_modules() {
		$modules = $this->get_modules();

		// Load all modules on the settings screen.
		if ( $this->is_dashboard_screen() ) {
			$module_ids = $modules->keys();
		} else {
			$module_ids = $modules->get_active_keys();
		}

		foreach ( $module_ids as $module_id ) {
			$modules[ $module_id ]->load();
		}
	}

	/**
	 * Whether the current request is the dashboard screen.
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	protected function is_dashboard_screen() {
		return is_admin() && isset( $_GET['page'] ) && 'audiotheme' == $_GET['page'];
	}
}
