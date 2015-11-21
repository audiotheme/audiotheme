<?php
/**
 * Plugin setup.
 *
 * @package AudioTheme
 * @since 1.9.0
 */

/**
 * Plugin setup class.
 *
 * @package AudioTheme
 * @since 1.9.0
 */
class AudioTheme_Setup {
	/**
	 * Plugin instance.
	 *
	 * @since 1.9.0
	 * @var AudioTheme_Plugin_AudioTheme
	 */
	protected $plugin;

	/**
	 * Set a reference to a plugin instance.
	 *
	 * @since 1.9.0
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
	 * @since 1.9.0
	 */
	public function register_hooks() {
		add_action( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );
		register_activation_hook( $this->plugin->get_file(),   array( $this, 'activate' ) );
		register_deactivation_hook( $this->plugin->get_file(), array( $this, 'deactivate' ) );
	}

	/**
	 * Flush the rewrite rules if needed.
	 *
	 * @since 1.9.0
	 */
	public function maybe_flush_rewrite_rules() {
		if ( is_network_admin() || 'no' !== get_option( 'audiotheme_flush_rewrite_rules' ) ) {
			return;
		}

		update_option( 'audiotheme_flush_rewrite_rules', 'no' );
		flush_rewrite_rules();
	}

	/**
	 * Activation routine.
	 *
	 * Occurs too late to flush rewrite rules, so set an option to flush them on
	 * the next request.
	 *
	 * @since 1.9.0
	 */
	public function activate() {
		update_option( 'audiotheme_flush_rewrite_rules', 'yes' );
	}

	/**
	 * Deactivation routine.
	 *
	 * Deleting the rewrite rules option should force WordPress to regenerate
	 * them next time they're needed.
	 *
	 * @since 1.9.0
	 */
	public function deactivate() {
		delete_option( 'rewrite_rules' );
	}

}
