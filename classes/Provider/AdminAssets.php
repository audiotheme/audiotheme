<?php
/**
 * Administration assets provider.
 *
 * @package   AudioTheme\Administration
 * @copyright Copyright 2012 AudioTheme
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Administration assets provider class.
 *
 * @package AudioTheme\Administration
 * @since   2.0.0
 */
class AudioTheme_Provider_AdminAssets extends AudioTheme_AbstractProvider {
	/**
	 * Register hooks.
	 *
	 * @since 2.0.0
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register admin scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function register_assets() {
		$base_url = set_url_scheme( $this->plugin->get_url( 'admin/js' ) );
		$suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'audiotheme-admin',     $base_url . '/admin.bundle' . $suffix . '.js', array( 'jquery-ui-sortable', 'underscore', 'wp-util' ), AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-dashboard', $base_url . '/dashboard.js',                   array( 'jquery', 'wp-backbone', 'wp-util' ),            AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-media',     $base_url . '/media' . $suffix . '.js',        array( 'jquery' ),                                      AUDIOTHEME_VERSION, true );
		wp_register_script( 'audiotheme-settings',  $base_url . '/settings' . $suffix . '.js',     array(),                                                AUDIOTHEME_VERSION, true );

		wp_localize_script( 'audiotheme-dashboard', '_audiothemeDashboardSettings', array(
			'canActivateModules' => current_user_can( 'activate_plugins' ),
			'modules'            => audiotheme()->modules->prepare_for_js(),
			'l10n'               => array(
				'activate'   => __( 'Activate', 'audiotheme' ),
				'deactivate' => __( 'Deactivate', 'audiotheme' ),
			),
		) );

		wp_localize_script( 'audiotheme-media', 'AudiothemeMediaControl', array(
			'audioFiles'      => __( 'Audio files', 'audiotheme' ),
			'frameTitle'      => __( 'Choose an Attachment', 'audiotheme' ),
			'frameUpdateText' => __( 'Update Attachment', 'audiotheme' ),
		) );

		$base_url = set_url_scheme( $this->plugin->get_url( 'admin/css' ) );

		wp_register_style( 'audiotheme-admin',     $base_url . '/admin.min.css' );
		wp_register_style( 'audiotheme-dashboard', $base_url . '/dashboard.min.css' );
	}

	/**
	 * Enqueue global admin scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_assets() {
		wp_enqueue_script( 'audiotheme-admin' );
		wp_enqueue_style( 'audiotheme-admin' );
	}
}
