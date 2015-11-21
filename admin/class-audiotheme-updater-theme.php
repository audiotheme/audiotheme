<?php
/**
 * Theme updater.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     1.0.0
 */

/**
 * Theme updater class.
 *
 * @package AudioTheme\Administration
 * @since   1.0.0
 */
class AudioTheme_Updater_Theme extends AudioTheme_Updater {
	/**
	 * Constructor. Sets up the theme updater object.
	 *
	 * Loops through the class properties and sets any that are passed in
	 * through the $args parameter.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Associative array to set object properties.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->type = 'theme';
		$this->id = ( ! empty( $args['slug'] ) ) ? sanitize_key( $args['slug'] ) : get_template();
		$this->slug = $this->id;

		$theme = wp_get_theme( $this->slug );
		$this->version = $theme->get( 'Version' );

		if ( is_child_theme() ) {
			$theme = wp_get_theme( get_stylesheet() );
			$this->api_data['template_version'] = $theme->get( 'Template Version' );
		}
	}

	/**
	 * Attach hooks to integrate with the WordPress update process.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Prevent WordPress from sending information about this theme to wordpress.org.
		add_filter( 'http_request_args', array( $this, 'disable_wporg_update_check' ), 5, 2 );

		// Check the external API whenever WordPress checks for updates to wordpress.org hosted themes.
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_transient' ) );

		// Add a message about the theme update on the Themes screen.
		add_action( 'load-themes.php', array( $this, 'load_themes_screen' ) );
	}

	/**
	 * Cast the standard check_for_update() response to an array for the theme
	 * update API.
	 *
	 * @since 1.0.0
	 *
	 * @param string $source Source to check for updates.
	 * @return object|false
	 */
	public function check_for_update( $source = 'transient' ) {
		$response = parent::check_for_update( $source );

		if ( $response ) {
			return (array) $response;
		}

		return false;
	}

	/**
	 * Add an admin notice to the Appearance->Themes screen about theme updates.
	 *
	 * @since 1.0.0
	 */
	public function load_themes_screen() {
		add_thickbox();
		add_action( 'admin_notices', array( $this, 'update_nag' ) );
	}

	/**
	 * Show a nag message if there is an update or an error during the update
	 * process.
	 *
	 * @since 1.0.0
	 */
	public function update_nag() {
		if ( ! current_user_can( 'update_themes' ) ) {
			return;
		}

		$notice = '';
		$api_response = get_transient( $this->transient_key() );

		if ( ! $api_response || empty( $api_response->status ) ) {
			return;
		}

		// Get the theme info.
		$theme = wp_get_theme( $this->slug );

		if ( isset( $api_response->theme->current_version ) && version_compare( $theme->get( 'Version' ), $api_response->theme->current_version, '<' ) ) {
			$notice = sprintf( _x( '<strong>%1$s %2$s</strong> is available.', 'theme name and version', 'audiotheme' ), $theme->get( 'Name' ), $api_response->theme->current_version ) . ' ';

			if ( 'ok' === $api_response->status ) {
				// If status is ok and there's a new version, display a message to update.
				$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $this->slug ), 'upgrade-theme_' . $this->slug );
				$update_onclick = ' onclick="if ( confirm(\'' . esc_js( __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", 'audiotheme' ) ) . '\') ) {return true;}return false;"';

				$notice .= sprintf( _x( '%1$s or %2$s.', 'theme changelog and update', 'audiotheme' ),
					sprintf( '<a href="%1$s" class="thickbox" title="%2$s">' . __( "Check out what's new", 'audiotheme' ) . '</a>',
						esc_url( add_query_arg( array( 'TB_iframe' => 'true', 'width' => 600, 'height' => 600 ), $api_response->wpargs->url ) ),
						esc_attr( $theme->get( 'Name' ) )
					),
					sprintf( '<a href="%1$s"%2$s>' . __( 'update now', 'audiotheme' ) . '</a>',
						$update_url,
						$update_onclick
					)
				);
			}
		}

		// If status isn't ok, display a reason why.
		if ( 'ok' !== $api_response->status ) {
			$notice_args = array();

			if ( ! empty( $notice ) ) {
				$notice_args['prepend'] = $notice;
			}

			// Merge default notices with the custom ones.
			$notices = wp_parse_args( $this->notices, $this->get_license_error_messages( $notice_args ) );

			// @todo framework_update_required & wordpress_update_required
			// Determine which notice to display.
			$notice = ( isset( $api_response->status ) && isset( $notices[ $api_response->status ] ) ) ? $notices[ $api_response->status ] : $notices['generic'];

			// A custom message from the API server.
			if ( ! empty( $api_response->notice ) ) {
				$notice = wp_kses_data( $api_response->notice );
			}
		}

		// Allow the notice to be filtered.
		$notice = apply_filters( 'audiotheme_update_theme_notice', $notice, $this->slug, $api_response, $theme );

		// Display the notice.
		if ( $notice ) {
			echo '<div id="update-nag">' . $notice . '</div>';
		}
	}
}
