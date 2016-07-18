<?php
/**
 * Internationalization provider.
 *
 * @package   AudioTheme\i18n
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Internationalization provider class.
 *
 * @package AudioTheme\i18n
 * @since   2.0.0
 */
class AudioTheme_Provider_i18n {
	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_Plugin
	 */
	protected $plugin;

	/**
	 * Set a reference to a plugin instance.
	 *
	 * @since 2.0.0
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
	 * Loads the text domain during the `plugins_loaded` action.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		if ( did_action( 'plugins_loaded' ) ) {
			$this->load_textdomain();
		} else {
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		}
	}

	/**
	 * Load the text domain to localize the plugin.
	 *
	 * @since 2.0.0
	 */
	public function load_textdomain() {
		$plugin_rel_path = dirname( $this->plugin->get_basename() ) . '/languages';
		load_plugin_textdomain( $this->plugin->get_slug(), false, $plugin_rel_path );
	}
}
