<?php
/**
 * Assets provider.
 *
 * @package AudioTheme
 * @since 1.9.0
 */

/**
 * Assets provider class.
 *
 * @package AudioTheme
 * @since 1.9.0
 */
class AudioTheme_Assets {
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
		add_action( 'wp_enqueue_scripts',    array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 1 );
	}

	/**
	 * Register frontend scripts and styles.
	 *
	 * @since 1.9.0
	 */
	public function register_assets() {
		global $wp_locale;

		$base_url = set_url_scheme( $this->plugin->get_url( 'includes/js' ) );
		$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'audiotheme',              $base_url  .'/audiotheme' . $suffix . '.js',          array( 'jquery', 'jquery-jplayer', 'jquery-fitvids' ), '1.0.0',  true );
		wp_register_script( 'jquery-fitvids',          $base_url  .'/vendor/jquery.fitvids.min.js',          array( 'jquery' ),                                     '1.1.0',  true );
		wp_register_script( 'jquery-jplayer',          $base_url  .'/vendor/jquery.jplayer.min.js',          array( 'jquery' ),                                     '2.2.19', true );
		wp_register_script( 'jquery-jplayer-playlist', $base_url  .'/vendor/jquery.jplayer.playlist.min.js', array( 'jquery-jplayer' ),                             '2.2.2',  true );
		wp_register_script( 'jquery-placeholder',      $base_url  .'/vendor/jquery.placeholder.min.js',      array( 'jquery' ),                                     '2.0.7',  true );
		wp_register_script( 'jquery-timepicker',       $base_url  .'/vendor/jquery.timepicker.min.js',       array( 'jquery' ),                                     '1.6.11', true );
		wp_register_script( 'moment',                  $base_url  .'/vendor/moment.min.js',                  array(),                                               '2.10.6', true );
		wp_register_script( 'pikaday',                 $base_url  .'/vendor/pikaday.min.js',                 array( 'moment'),                                      '1.4.0',  true );

		wp_localize_script( 'jquery-jplayer', 'AudiothemeJplayer', array(
			'swfPath' => $base_url . '/vendor',
		) );

		wp_localize_script( 'pikaday', '_pikadayL10n', array(
			'previousMonth' => __( 'Previous Month', 'audiotheme' ),
			'nextMonth'     => __( 'Next Month', 'audiotheme' ),
			'months'        => array_values( $wp_locale->month ),
			'weekdays'      => $wp_locale->weekday,
			'weekdaysShort' => array_values( $wp_locale->weekday_abbrev ),
		) );

		wp_register_style( 'audiotheme', $this->plugin->get_url( 'includes/css/audiotheme.min.css' ) );
	}
}
