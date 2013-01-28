<?php
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * The plugin update class for AudioTheme.
 *
 * @see wp-includes/update.php
 * @see wp-admin/includes/update.php
 * @see wp-admin/includes/plugin-install.php
 *
 * @package AudioTheme_Framework
 *
 * @since 1.0.0
 *
 * @todo Account for a license?
 */
class Audiotheme_Updater_Plugin extends Audiotheme_Updater {
	/**
	 * Absolute path to the plugin file.
	 * @var string
	 */
	protected $file;

	/**
	 * Constructor. Sets up the plugin updater object.
	 *
	 * Loops through the class properties and sets any that are passed in
	 * through the $args parameter.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Associative array to set object properties.
	 * @param string $file Absolute path a plugin file.
	 */
	public function __construct( $args = array(), $file ) {
		parent::__construct( $args );

		$this->type = 'plugin';
		$this->file = $file;
		$this->id = plugin_basename( $file );

		if ( empty( $this->slug ) ) {
			$this->slug = basename( $this->id, '.php' );
		}

		$plugin = get_plugin_data( $this->file );
		$this->version = $plugin['Version'];
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_wporg_update_uri() {
		return 'api.wordpress.org/plugins/update-check';
	}

	/**
	 * Attach hooks to integrate with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Prevent WordPress from sending information about this plugin to wordpress.org.
		add_filter( 'http_request_args', array( $this, 'disable_wporg_update_check' ), 5, 2 );

		// Check the external API whenever WordPress checks for updates to wordpress.org hosted plugins.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_transient' ), 100 );

		// Filter the plugin API to route requests for this plugin to the external API.
		add_filter( 'plugins_api', array( $this, 'plugins_api' ), 5, 3 );

		// Add a message about the plugin update on the Plugins screen.
		add_action( 'after_plugin_row_' . $this->id,   array( $this, 'after_plugin_row' ), 10, 2 );
	}

	/**
	 * Filter the plugin API requests for this plugin to use the external API
	 * instead.
	 *
	 * @see plugins_api()
	 *
	 * @since 1.0.0
	 *
	 * @param bool|object $data False if the first filter, otherwise a $response object from an earlier filter.
	 * @param string $action The API method name.
	 * @param array|object $args Arguments to serialize for the Plugin Info API.
	 * @return object plugins_api response object on success, WP_Error on failure.
	 */
	public function plugins_api( $data, $action, $args ) {
		// Bail if this isn't an information request for this plugin.
		if ( 'plugin_information' != $action || empty( $args->slug ) || $args->slug != $this->id ) {
			return $data;
		}

		$response = $this->api_request( array(
			'entity' => 'plugin',
			'method' => 'info',
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'plugins_api_failed', __( 'An unexpected error occurred. Something may be wrong with the update API or this server&#8217;s configuration.', 'audiotheme-i18n' ), $response );
		}

		if ( isset( $response->sections ) ) {
			$response->sections = (array) $response->sections;
		}

		return $response;
	}

	/**
	 * Show a nag message after the plugin row on the Plugins screen if the
	 * API didn't return a status of 'ok'.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_file Plugin basename.
	 * @param array $plugin_data Data from the plugin's headers.
	 */
	public function after_plugin_row( $plugin_file, $plugin_data ) {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$api_response = get_transient( $this->transient_key() );

		if ( isset( $api_response->status ) && 'ok' !== $api_response->status ) {
			if ( isset( $api_response->plugin->current_version ) && version_compare( $plugin_data['Version'], $api_response->plugin->current_version, '<' ) ) {
				$update_available = true;
				$notice = sprintf( _x( '%1$s %2$s is available.', 'plugin name and version', 'audiotheme-i18n' ), $plugin_data['Name'], $api_response->plugin->current_version ) . ' ';
			}

			// Default notices.
			$notices['empty_license']  = $notice . __( 'Register your copy to receive automatic updates and support. Need a license key?', 'audiotheme-i18n' ) . ' ';
			$notices['empty_license'] .= sprintf( '<a href="%s">' . __( 'Purchase one now.', 'audiotheme-i18n' ) . '</a>', esc_url( $plugin_data['PluginURI'] ) );

			$notices['invalid_license']  = $notice . __( 'Your license key appears to be invalid.', 'audiotheme-i18n' ) . ' ';
			$notices['invalid_license'] .= sprintf( __( 'Verify that is has been <a href="%1$s">entered correctly</a> or <a href="%2$s">purchase one now.</a>', 'audiotheme-1i8n' ),
				admin_url( 'options-general.php?page=wp-query-manager#show-settings-link' ),
				esc_url( $plugin_data['PluginURI'] )
			);

			// @todo Can occur if the activation limit has been reached.
			$notices['not_activated']  = $notice . __( 'Your license has not been activated for this site.', 'audiotheme-18n' );

			$notices['expired_license']  = $notice . __( 'Your license has expired.', 'audiotheme-18n' ) . ' ';
			$notices['expired_license'] .= sprintf( '<a href="%1$s">' . __( 'Renew here.', 'audiotheme-1i8n' ) . '</a>', esc_url( $plugin_data['PluginURI'] ) );

			// @todo requires_purchase
			// @todo wordpress_update_required

			$notices['generic'] = __( 'An unexpected error occurred while checking the update server.', 'audiotheme-1i8n' );

			// Merge default notices with the custom ones.
			$notices = wp_parse_args( $this->notices, $notices );

			// Determine which notice to display and allow it to be filtered.
			$notice = ( isset( $notices[ $api_response->status ] ) ) ? $notices[ $api_response->status ] : $notices['generic'];
			$notice = apply_filters( 'audiotheme_plugin_update_notice-' . $this->slug, $notice, $api_response, $plugin_data, $plugin_file );

			// Finally display the notice.
			if ( $notice ) {
				echo '</tr><tr class="plugin-update-tr"><td class="plugin-update colspanchange" colspan="3"><div class="update-message">' . $notice . '</div></td>';
			}
		}
	}
}
