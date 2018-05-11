<?php
/**
 * Google Maps provider.
 *
 * @package   AudioTheme
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Google Maps provider class.
 *
 * @package AudioTheme
 * @since   2.0.0
 */
class AudioTheme_Provider_Setting_GoogleMaps extends AudioTheme_AbstractProvider {
	/**
	 * API key option name.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const API_KEY_OPTION_NAME = 'audiotheme_google_maps_api_key';

	/**
	 * Option group.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $page = 'audiotheme-settings';

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		if ( is_network_admin() ) {
			$this->page = 'audiotheme-network-settings';
		}
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init',                       array( $this, 'register_settings' ) );
		add_action( 'admin_init',                       array( $this, 'register_sections' ) );
		add_action( 'admin_init',                       array( $this, 'register_fields' ) );
		add_action( 'audiotheme_save_network_settings', array( $this, 'save_network_settings' ) );
	}

	/**
	 * Register the settings option.
	 *
	 * @since 2.0.0
	 */
	public function register_settings() {
		register_setting(
			$this->page,
			self::API_KEY_OPTION_NAME,
			'sanitize_text_field'
		);
	}

	/**
	 * Add settings sections.
	 *
	 * @since 2.0.0
	 */
	public function register_sections() {
		add_settings_section(
			'google-maps',
			__( 'Google Maps', 'audiotheme' ),
			array( $this, 'display_section_description' ),
			$this->page
		);
	}

	/**
	 * Register settings fields.
	 *
	 * @since 2.0.0
	 */
	public function register_fields() {
		add_settings_field(
			'google-maps-api-key',
			__( 'API Key', 'audiotheme' ),
			array( $this, 'render_field_api_key' ),
			$this->page,
			'google-maps',
			array( 'label_for' => 'audiotheme-google-maps-api-key' )
		);
	}

	/**
	 * Display the section description.
	 *
	 * @since 2.0.0
	 */
	public function display_section_description() {
		?>
		<p>
			<?php echo $this->allowed_html( __( 'As of June 22, 2016, Google requires an API key to display maps on a website. If you don\'t have one, follow this guide to <a href="https://audiotheme.com/support/kb/create-google-maps-api-key/" target="_blank">create an API key</a>.', 'audiotheme' ) ); ?>
		</p>
		<?php
		$api_key = $this->get_api_key();
		if ( ! empty( $api_key ) ) {
			$this->test_static_api( $api_key );
			$this->test_javascript_api( $api_key );
		}
	}

	/**
	 * Display a field for defining the vendor.
	 *
	 * @since 2.0.0
	 */
	public function render_field_api_key() {
		if ( is_network_admin() ) {
			$api_key = get_site_option( self::API_KEY_OPTION_NAME, '' );
		} else {
			$api_key = get_option( self::API_KEY_OPTION_NAME, '' );
		}
		?>
		<p>
			<input type="text" name="<?php echo self::API_KEY_OPTION_NAME; ?>" id="audiotheme-google-maps-api-key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text"><br>
		</p>
		<?php
	}

	/**
	 * Manually save network settings.
	 *
	 * @since 2.0.0
	 */
	public function save_network_settings() {
		$is_valid_nonce = ! empty( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'audiotheme-network-settings-options' );

		if ( ! is_network_admin() || ! $is_valid_nonce ) {
			return;
		}

		// Update the API key.
		if ( isset( $_POST[ self::API_KEY_OPTION_NAME ] ) ) {
			$value = sanitize_text_field( $_POST[ self::API_KEY_OPTION_NAME ] );
			update_site_option( self::API_KEY_OPTION_NAME, $value );
		}
	}

	/**
	 * Retrieve the Google Maps API key.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	protected function get_api_key() {
		return $this->plugin->modules['gigs']->get_google_maps_api_key();
	}

	/**
	 * Allow only the allowedtags array in a string.
	 *
	 * @since 2.0.0
	 *
	 * @link https://www.tollmanz.com/wp-kses-performance/
	 *
	 * @param  string $string The unsanitized string.
	 * @return string         The sanitized string.
	 */
	protected function allowed_html( $html ) {
		return wp_kses( $html, array( 'a' => array( 'href' => true, 'target' => true ) ) );
	}

	/**
	 * Load the Google Maps JavaScript API and listen for authentication errors.
	 *
	 * @since 2.0.0
	 */
	protected function test_javascript_api( $api_key ) {
		?>
		<script>
		function gm_authFailure( param ) {
			document.getElementById( 'audiotheme-google-maps-auth-error-notice' ).style.display = 'block';
		}
		</script>

		<script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=<?php echo sanitize_text_field( $api_key ); ?>"></script>

		<div id="audiotheme-google-maps-auth-error-notice" class="notice notice-error inline" style="display: none">
			<p>
				<?php echo $this->allowed_html( __( 'An error was detected with your Google Maps API key. Check your <a href="https://developers.google.com/maps/documentation/javascript/error-messages#checking-errors">browser\'s JavaScript console</a> for more information.', 'audiotheme' ) ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Test the Google Static Maps API.
	 *
	 * @since 2.0.0
	 */
	protected function test_static_api( $api_key ) {
		$url = add_query_arg( array(
			'center' => urlencode( 'Gruene Hall, 1281 Gruene Road, New Braunfels, TX 78130' ),
			'size'   => '100x100',
			'format' => 'png8',
			'key'    => $api_key,
		), 'https://maps.googleapis.com/maps/api/staticmap' );

		$response = wp_remote_get( $url, array(
			'headers' => array(
				'Referer' => esc_url_raw( home_url( '/' ) ),
			),
		) );

		if ( 403 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}
		?>
		<div class="notice notice-error inline">
			<p>
				<?php echo $this->allowed_html( __( 'An error was detected with your API key. Verify the key and ensure the Google Static Maps API is enabled in the <a href="https://console.developers.google.com/apis/dashboard" target="_blank">Google Developers Console</a>.', 'audiotheme' ) ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Test the Google Maps Places Web Service API for authentication errors.
	 *
	 * @since 2.0.0
	 */
	protected function test_places_api() {
		$url = add_query_arg( array(
			'placeid' => 'ChIJ_xNwmcWiXIYRFo5WwM_gkkU', // Gruene Hall
			'key'     => $this->get_api_key(),
		), 'https://maps.googleapis.com/maps/api/place/details/json' );

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return;
		}

		$json = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'OK' !== $json->status && isset( $json->error_message ) ) {
			printf(
				'<div class="notice notice-error">%s</div>',
				wpautop( make_clickable( esc_html( $json->error_message ) ) )
			);
		}
	}
}
