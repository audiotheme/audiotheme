<?php
/**
 * Update manager
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Update manager class.
 *
 * @package AudioTheme\Administration
 * @since   2.0.0
 */
class AudioTheme_UpdateManager extends AudioTheme_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		if ( function_exists( 'audiotheme_agent' ) ) {
			return;
		}

		if ( ! is_multisite() || is_network_admin() ) {
			add_action( 'init', array( $this, 'update' ) );
		}

		// Don't send the remote request if a license key hasn't been entered.
		if ( ! $this->plugin->license->has_key() ) {
			add_filter( 'do_audiotheme_update_request',               '__return_false' );
			add_filter( 'audiotheme_update_plugin_notice-audiotheme', array( $this, 'display_audiotheme_update_notice' ) );
		}
	}

	/**
	 * Check for AudioTheme and theme updates.
	 *
	 * @since 2.0.0
	 */
	public function update() {
		$api_data = array( 'license' => $this->plugin->license->get_key() );

		$this->update_audiotheme( $api_data );
		$this->update_theme( $api_data );
		$this->update_network_themes( $api_data );
	}

	/**
	 * Display a notice to register if the license key is empty.
	 *
	 * @since 2.0.0
	 *
	 * @param string $notice The default notice.
	 * @return string
	 */
	public function display_audiotheme_update_notice( $notice ) {
		$settings_page = is_network_admin() ? 'network/settings.php' : 'admin.php';

		$notice  = sprintf(
			__( '<a href="%s">Register your copy of AudioTheme</a> to receive automatic updates and support. Need a license key?', 'audiotheme' ),
			esc_url( add_query_arg( 'page', 'audiotheme-settings', admin_url( $settings_page ) ) )
		);
		$notice .= ' <a href="https://audiotheme.com/view/audiotheme/" target="_blank">' . __( 'Purchase one now.', 'audiotheme' ) . '</a>';

		return $notice;
	}

	/**
	 * Set up the plugin updater.
	 *
	 * @since 2.0.0
	 *
	 * @param array $api_data Extra data to send to the update API.
	 */
	protected function update_audiotheme( $api_data = array() ) {
		$updater = new AudioTheme_Updater_Plugin(
			array( 'api_data' => $api_data ),
			$this->plugin->get_file()
		);

		$updater->init();
	}

	/**
	 * Set up the theme updater.
	 *
	 * @since 2.0.0
	 *
	 * @param array $api_data Extra data to send to the update API.
	 */
	protected function update_theme( $api_data = array() ) {
		if ( ! current_theme_supports( 'audiotheme-automatic-updates' ) ) {
			return;
		}

		$support = get_theme_support( 'audiotheme-automatic-updates' );
		$args    = wp_parse_args( $support[0], array( 'api_data' => $api_data ) );

		$theme_updater = new AudioTheme_Updater_Theme( $args );
		$theme_updater->init();

		// @todo Does this happen on every request?
		// Add the theme to a list to check for updates in multisite.
		$this->update_themes_list( get_template(), $support[0] );
	}

	/**
	 * Check for updates for all themes in network installations.
	 *
	 * @todo Implement bulk updating.
	 *
	 * @since 2.0.0
	 *
	 * @param array $api_data Extra data to send to the update API.
	 */
	protected function update_network_themes( $api_data = array() ) {
		if ( ! is_network_admin() ) {
			return;
		}

		$themes = get_site_option( 'audiotheme_themes' );
		if ( empty( $themes ) ) {
			return;
		}

		// Filter out invalid theme slugs.
		$check = array_intersect_key( $themes, wp_get_themes() );

		if ( $check ) {
			foreach ( $check as $slug => $args ) {
				$args    = wp_parse_args( $args, array( 'api_data' => $api_data ) );
				$updater = new AudioTheme_Updater_Theme( $args );
				$updater->init();
			}
		}

		if ( count( $check ) !== count( $themes ) ) {
			update_site_option( 'audiotheme_themes', $check );
		}
	}

	/**
	 * Add AudioTheme themes to a site option so they can be checked for updates
	 * when in multsite mode.
	 *
	 * @since 2.0.0
	 *
	 * @param string $theme Theme slug.
	 * @param array  $api_args Optional. Arguments to send to the remote API.
	 */
	protected function update_themes_list( $theme, $api_args = array() ) {
		if ( ! is_multisite() ) {
			return;
		}

		$themes = (array) get_site_option( 'audiotheme_themes', array() );

		if ( ! array_key_exists( $theme, $themes ) || $themes[ $theme ] !== $api_args ) {
			$themes[ $theme ] = wp_parse_args( $api_args, array( 'slug' => $theme ) );
			update_site_option( 'audiotheme_themes', $themes );
		}
	}
}
