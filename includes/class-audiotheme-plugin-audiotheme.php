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
	 * Magic get method.
	 *
	 * @since 1.9.0
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
	 * @since 1.9.0
	 */
	public function load() {
		$this->load_modules();
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
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	protected function is_dashboard_screen() {
		return is_admin() && isset( $_GET['page'] ) && 'audiotheme' == $_GET['page'];
	}
}
