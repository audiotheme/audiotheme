<?php
/**
 * Base admin screen functionality.
 *
 * @package AudioTheme\Administration
 * @since 1.9.0
 */

/**
 * Base screen class.
 *
 * @package AudioTheme\Administration
 * @since 1.9.0
 */
abstract class AudioTheme_Screen {
	/**
	 * Plugin instance.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Plugin
	 */
	protected $plugin;

	/**
	 * Set the plugin instance.
	 *
	 * @since 1.9.0
	 *
	 * @param AudioTheme_Plugin $plugin Plugin instance.
	 * @return $this
	 */
	public function set_plugin( $plugin ) {
		$this->plugin = $plugin;
		return $this;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.0
	 */
	public function register_hooks() {}
}
