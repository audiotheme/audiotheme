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
class AudioTheme_Provider_Setting_GoogleMaps {
	/**
	 * API key option name.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	const API_KEY_OPTION_NAME = 'audiotheme_google_maps_api_key';

	/**
	 * Plugin instance.
	 *
	 * @since 2.0.0
	 * @var AudioTheme_Plugin
	 */
	protected $plugin;

	/**
	 * Option group.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $page = 'audiotheme-settings';

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
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'register_sections' ) );
		add_action( 'admin_init', array( $this, 'register_fields' ) );
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
	 * Display the license section.
	 *
	 * @since 2.0.0
	 */
	public function display_section_description() {
		?>
		<p>
			<?php esc_html_e( 'Enter your Google Maps API key below.')?>
		</p>
		<?php
	}

	/**
	 * Display a field for defining the vendor.
	 *
	 * @since 2.0.0
	 */
	public function render_field_api_key() {
		$value = get_option( self::API_KEY_OPTION_NAME, '' );
		?>
		<p>
			<input type="text" name="<?php echo self::API_KEY_OPTION_NAME; ?>" id="audiotheme-google-maps-api-key" value="<?php echo esc_attr( $value ); ?>" class="regular-text"><br>
		</p>
		<?php
	}
}
