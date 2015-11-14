<?php
/**
 * License functionality.
 *
 * @package AudioTheme
 * @since   1.9.0
 */

/**
 * License class.
 *
 * @package AudioTheme
 * @since   1.9.0
 */
class AudioTheme_License {
	/**
	 * Option name for storing the key.
	 *
	 * @since 1.9.0
	 * @var string
	 */
	const OPTION_NAME = 'audiotheme_license_key';

	/**
	 * Remote API URL.
	 * @var string
	 */
	protected $api_url = 'https://audiotheme.com/api/';

	/**
	 * Constructor method.
	 *
	 * @since 1.9.0
	 *
	 * @param string $key License key.
	 */
	public function __construct( $key = null ) {
		$this->set_key( $key );
	}

	/**
	 * Whether a key has been saved.
	 *
	 * @since 1.9.0
	 *
	 * @return boolean
	 */
	public function has_key() {
		$key = $this->get_key();
		return ! empty( $key );
	}

	/**
	 * Retrieve the license key.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_key() {
		if ( empty( $this->key ) ) {
			$this->key = get_option( self::OPTION_NAME, '' );
		}
		return $this->key;
	}

	/**
	 * Set the license key.
	 *
	 * @since 1.9.0
	 *
	 * @param string $key License key.
	 * @return $this
	 */
	public function set_key( $key ) {
		$this->key = $key;
		return $this;
	}

	/**
	 * Whether the key is valid.
	 *
	 * @since 1.9.0
	 *
	 * @return boolean
	 */
	public function is_valid() {
		$is_valid = false;

		if ( $this->has_key() ) {
			$status = get_option( 'audiotheme_license_status' );
			$is_valid = isset( $status->status ) && 'ok' === $status->status;
		}

		return $is_valid;
	}

	/**
	 * Activate the current site.
	 *
	 * @since 1.9.0
	 *
	 * @return object|WP_Error
	 */
	public function activate() {
		$response = $this->send_request( array(
			'method' => 'activate',
		) );

		update_option( 'audiotheme_license_status', $response );

		return $response;
	}

	/**
	 * Clear the status.
	 *
	 * Forces the key to be reactivated.
	 *
	 * @since 1.9.0
	 *
	 * @return $this
	 */
	public function reset() {
		update_option( 'audiotheme_license_status', '' );
		return $this;
	}

	/**
	 * Save the license key.
	 *
	 * @since 1.9.0
	 *
	 * @return $this
	 */
	public function save() {
		update_option( self::OPTION_NAME, $this->get_key() );
		return $this;
	}

	/**
	 * Send a remote API request.
	 *
	 * @since 1.9.0
	 *
	 * @param array $args Arguments to send to the API endpoint.
	 * @return object JSON-decoded response or WP_Error on failure.
	 */
	protected function send_request( $args ) {
		$defaults = array(
			'entity'  => 'license',
			'license' => $this->get_key(),
			'url'     => home_url(),
		);

		$response = wp_remote_post(
			apply_filters( 'audiotheme_license_api_url', $this->api_url ),
			array(
				'body'      => $args = array_merge( $defaults, $args ),
				'headers'   => array(
					'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ),
					'Referer'      => home_url(),
				),
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'bad_response', __( 'Bad response.', 'audiotheme' ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}
}
