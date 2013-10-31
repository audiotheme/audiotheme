<?php
/**
 * The base update class for AudioTheme.
 *
 * @package AudioTheme_Framework
 *
 * @since 1.0.0
 */
class Audiotheme_Updater {
	/**
	 * @var string
	 */
	protected $api_url = 'http://audiotheme.com/api/';

	/**
	 * Additional data to send to pass through the API.
	 * @var array
	 */
	protected $api_data = array();

	/**
	 * Entity type.
	 * @var string
	 */
	protected $type;

	/**
	 * Entity id.
	 * @var string
	 */
	protected $id;

	/**
	 * Entity slug. Ex: plugin-name or theme-name
	 * @var string
	 */
	protected $slug;

	/**
	 * Entity version number.
	 * @var string
	 */
	protected $version;

	/**
	 * An associative array of update notices to display depending on the
	 * server response.
	 * @var array
	 */
	protected $notices = array();

	/**
	 * Constructor. Sets up the theme updater object.
	 *
	 * Loops through the class properties and sets any that are passed in
	 * through the $args parameter.
	 *
	 * This should be instantiated and hooked up before init:10 so it'll be
	 * processed during WP Cron events.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Associative array to set object properties.
	 */
	public function __construct( $args = array() ) {
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}
	}

	/**
	 *
	 */
	public function init() { }

	/**
	 * Disable update requests to wordpress.org for the entity.
	 *
	 * Inactive plugins/themes will still hit the wordpress.org API.
	 *
	 * @link http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
	 * @link https://github.com/cftp/external-update-api/blob/281a0efbf6c2085cbd8c3d49814fce97c59a63b4/external-update-api/euapi.php#L45
	 * @see WP_Http::request()
	 *
	 * @since 1.0.0
	 *
	 * @param array $r Request args.
	 * @param string $url URI resource.
	 * @return array Filtered request args.
	 */
	public function disable_wporg_update_check( $r, $url ) {
		// If this isn't an update request, bail immediately.
		if ( false === strpos( $url, 'api.wordpress.org' ) || ! preg_match( '#://api\.wordpress\.org/(?P<type>plugins|themes)/update-check/(?P<version>[0-9.]+)/#', $url, $matches ) ) {
			return $r;
		}

		$api_type = $matches['type'];
		$api_version = floatval( $matches['version'] );

		$entities = $r['body'][ $api_type ];
		$entities = ( 1.0 == $api_version ) ? unserialize( $entities ) : json_decode( $entities, true );

		if ( 'plugins' == $api_type ) {
			$entities = (array) $entities;

			unset( $entities['plugins'][ $this->id ] );
			unset( $entities['active'][ array_search( $this->id, $entities['active'] ) ] );

			// Cast back to an object.
			if ( 1.0 == $api_version ) {
				$entities = (object) $entities;
			}
		} elseif ( 'themes' == $api_type ) {
			unset( $entities[ $this->id ] );
		}

		$r['body'][ $api_type ] = ( 1.0 == $api_version ) ? serialize( $entities ) : json_encode( $entities );

		return $r;
	}

	/**
	 * Filter the core update transients to add external update information.
	 *
	 * WordPress sets the "update_*" transients twice when doing a request for
	 * updates. The remote API shouldn't be hit twice, so the "last_checked"
	 * property is stored on the first pass and if it's the same on subsequent
	 * passes, the transient will be utilized if it's available.
	 *
	 * @see wp_update_plugins()
	 * @see wp_update_themes()
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Entity version and update information.
	 * @return array
	 */
	public function update_transient( $value ) {
		$data_source = ( ! empty( $this->last_checked ) && $this->last_checked == $value->last_checked ) ? 'transient' : 'api';
		$this->last_checked = $value->last_checked;

		$update_data = $this->check_for_update( $data_source );

		if ( $update_data ) {
			$value->response[ $this->id ] = $update_data;
		}

		return $value;
	}

	/**
	 * Check for an update.
	 *
	 * Checks the custom, theme-specific transient before doing a remote
	 * request. If the request fails, the transient is changed so checks are
	 * made every three hours.
	 *
	 * @since 1.0.0
	 *
	 * @param string $source Whether data should be returned from transient if available.
	 * @return bool|object Update args expected by WordPress API or false if there isn't an update.
	 */
	public function check_for_update( $source = 'transient' ) {
		$response = ( 'transient' == $source ) ? get_transient( $this->transient_key() ) : false;

		if ( ! $response && apply_filters( 'do_audiotheme_update_request', true, $this ) ) {
			$response = $this->api_request( array(
				'entity' => $this->type,
				'method' => 'update',
			) );

			if ( is_wp_error( $response ) ) {
				$data = new stdClass;
				$data->status = $response->get_error_code();

				// If the response failed, try again in 3 hours.
				set_transient( $this->transient_key(), $data, strtotime( '+3 hours' ) );
			} else {
				// Set the basename for the API. Unnecessary for themes.
				if ( 'plugin' == $this->type ) {
					if ( ! isset( $response->wpargs ) ) {
						$response->wpargs = new stdClass;
					}

					$response->wpargs->slug = $this->id;
				}

				set_transient( $this->transient_key(), $response, strtotime( '+12 hours' ) );
			}
		}

		// Bail if the response status isn't 'ok'.
		if ( ! isset( $response->status ) || 'ok' !== $response->status ) {
			do_action( 'audiotheme_update_response_error', $response );
			return false;
		}

		// Bail if there isn't a new version.
		if ( ! isset( $response->wpargs->new_version ) || version_compare( $this->version, $response->wpargs->new_version, '>=' ) ) {
			return false;
		}

		return $response->wpargs;
	}

	/**
	 * Activate a license key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key License key.
	 * @return object
	 */
	public function activate_license( $key ) {
		$response = $this->api_request( array(
			'entity'  => 'license',
			'method'  => 'activate',
			'license' => $key,
		) );

		return $response;
	}

	/**
	 * Do a remote API request.
	 *
	 * Merges the $api_data property, default arguments, and the $args
	 * parameter and sends them to the API for processing.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments to send to the API endpoint.
	 * @return object JSON-decoded response or WP_Error on failure.
	 */
	public function api_request( $args ) {
		global $wpdb;

		$defaults = array(
			'audiotheme' => AUDIOTHEME_VERSION,
			'language'   => WPLANG,
			'mysql'      => $wpdb->db_version(),
			'php'        => phpversion(),
			'slug'       => $this->slug,
			'url'        => home_url(),
			'version'    => $this->version,
			'wp'         => get_bloginfo( 'version' ),
		);

		$args = array_merge( $this->api_data, $defaults, $args );

		$response = wp_remote_post(
			apply_filters( 'audiotheme_update_api_url', $this->api_url ),
			array(
				'body'      => $args,
				'headers'   => array(
					'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ),
					'Referer'      => home_url(),
				),
				'sslverify' => false,
				'timeout'   => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 5 ),
			)
		);

		// Make sure the response was successful.
		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'bad_response', __( 'Bad response.', 'audiotheme' ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Key for the transient containing the update check request.
	 *
	 * Transient keys can only be 45 characters, so the prefix has been
	 * shortened to allow more space for the plugin name. Could change to use
	 * a hash if they get too long.
	 *
	 * @since 1.0.0
	 */
	protected function transient_key() {
		return 'atup_' . $this->slug;
	}

	/**
	 * Build default notices for license errors.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments to change output.
	 * @return array
	 */
	public function get_license_error_messages( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'prepend'       => '',
			'account_url'   => 'http://audiotheme.com/account/',
			'framework_url' => 'http://audiotheme.com/view/audiotheme/',
			'product_url'   => 'http://audiotheme.com/shop/',
		) );

		$messages['empty_license']  = $args['prepend'];
		$messages['empty_license'] .= sprintf( __( '<a href="%s">Register your copy of AudioTheme</a> to receive automatic updates and support. Need a license key?', 'audiotheme' ),
			esc_url( add_query_arg( 'page', 'audiotheme-settings', admin_url( 'admin.php' ) ) )
		);
		$messages['empty_license'] .= sprintf( ' <a href="%s" target="_blank">' . __( 'Purchase one now.', 'audiotheme' ) . '</a>',
			esc_url( $args['framework_url'] )
		);

		$messages['invalid_license']  = $args['prepend'];
		$messages['invalid_license']  = __( 'Your license key appears to be invalid.', 'audiotheme' ) . ' ';
		$messages['invalid_license'] .= sprintf( __( 'Verify that is has been <a href="%1$s">entered correctly</a> or <a href="%2$s" target="_blank">purchase one now.</a>', 'audiotheme-1i8n' ),
			esc_url( add_query_arg( 'page', 'audiotheme-settings', admin_url( 'admin.php' ) ) ),
			esc_url( $args['framework_url'] )
		);

		$messages['not_activated']  = $args['prepend'];
		$messages['not_activated']  = __( 'Your license has not been activated for this site.', 'audiotheme-18n' );
		$messages['not_activated'] .= ' ' . sprintf( __( 'Manage your site activations in <a href="%s" target="_blank">your account on AudioTheme.com</a>.', 'audiotheme' ),
			esc_url( $args['account_url'] )
		);

		$messages['expired_license']  = $args['prepend'];
		$messages['expired_license']  = __( 'Your AudioTheme license has expired.', 'audiotheme-18n' ) . ' ';
		$messages['expired_license'] .= sprintf( '<a href="%1$s">' . __( 'Renew here.', 'audiotheme-1i8n' ) . '</a>',
			esc_url( $args['framework_url'] )
		);

		$messages['generic'] = __( 'An unexpected error occurred while checking the update server.', 'audiotheme-1i8n' );

		return $messages;
	}
}
