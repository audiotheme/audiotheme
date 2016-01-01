<?php
/**
 * Base admin screen functionality.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.9.0
 */

/**
 * Base screen class.
 *
 * @package AudioTheme\Administration
 * @since   1.9.0
 */
abstract class AudioTheme_Screen_AbstractScreen {
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
	abstract public function register_hooks();
}
