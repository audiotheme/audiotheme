<?php
/**
 * Common plugin functionality.
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
class AudioTheme_Plugin {
	/**
	 * Absolute path to the main plugin directory.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $directory;

	/**
	 * Absolute path to the main plugin file.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $file;

	/**
	 * Modules.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Module_Collection
	 */
	protected $modules;

	/**
	 * Plugin identifier.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $slug;

	/**
	 * URL to the main plugin directory.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	protected $url;

	/**
	 * Load the plugin.
	 *
	 * @since 1.9.0
	 */
	public function load() {
		$this->modules = new AudioTheme_Module_Collection();

		$this->get_modules()->register_module( 'gigs', new AudioTheme_Module_Gigs() );
		$this->get_modules()->register_module( 'discography', new AudioTheme_Module_Discography() );
		$this->get_modules()->register_module( 'videos', new AudioTheme_Module_Videos() );

		$this->load_modules();
	}

	/**
	 * Register a hook provider.
	 *
	 * @since 1.9.0
	 *
	 * @param  object $provider Hook provider.
	 * @return $this
	 */
	public function register_hooks( $provider ) {
		$provider->register_hooks();
		return $this;
	}

	/**
	 * Retrieve the plugin directory.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_directory() {
		return $this->directory;
	}

	/**
	 * Set the plugin's directory.
	 *
	 * @since 1.9.0
	 *
	 * @param  string $directory Absolute path to the main plugin directory.
	 * @return $this
	 */
	public function set_directory( $directory ) {
		$this->directory = rtrim( $directory, '/' ) . '/';
		return $this;
	}

	/**
	 * Retrieve the path to a file in the plugin.
	 *
	 * @since 1.9.0
	 *
	 * @param  string $path Optional. Path relative to the plugin root.
	 * @return string
	 */
	public function get_path( $path = '' ) {
		return $this->directory . ltrim( $path, '/' );
	}

	/**
	 * Retrieve the absolute path for the main plugin file.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_file() {
		return $this->file;
	}

	/**
	 * Set the path to the main plugin file.
	 *
	 * @since 1.9.0
	 *
	 * @param  string $file Absolute path to the main plugin file.
	 * @return $this
	 */
	public function set_file( $file ) {
		$this->file = $file;
		return $this;
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
	 * Retrieve the plugin indentifier.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->directory;
	}

	/**
	 * Set the plugin identifier.
	 *
	 * @since 1.9.0
	 *
	 * @param  string $slug Plugin identifier.
	 * @return $this
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
		return $this;
	}

	/**
	 * Retrieve the URL for a file in the plugin.
	 *
	 * @since 1.9.0
	 *
	 * @param  string $path Optional. Path relative to the plugin root.
	 * @return string
	 */
	public function get_url( $path = '' ) {
		return $this->url . ltrim( $path, '/' );
	}

	/**
	 * Set the URL for plugin directory root.
	 *
	 * @since 1.9.0
	 *
	 * @param  string $url URL to the root of the plugin directory.
	 * @return $this
	 */
	public function set_url( $url ) {
		$this->url = rtrim( $url, '/' ) . '/';
		return $this;
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