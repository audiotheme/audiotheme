<?php
/**
 * Deprecated functions.
 *
 * These will be removed in a future version.
 *
 * @package AudioTheme\Deprecated
 */

/**
 * Custom user contact fields.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $contactmethods List of contact methods.
 * @return array
 */
function audiotheme_edit_user_contact_info( $contactmethods ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
	$contactmethods['twitter'] = 'Twitter Username';
	$contactmethods['facebook'] = 'Facebook URL';
	return $contactmethods;
}

/**
 * Retrieve system data.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @return array
 */
function audiotheme_system_info( $args = array() ) {
	global $wpdb;

	_deprecated_function( __FUNCTION__, '1.9.0' );

	$args = wp_parse_args( $args, array(
		'format' => '',
	) );

	$theme = wp_get_theme( get_template() );

	$data = array(
		'home_url' => array(
			'label' => 'Home URL',
			'value' => home_url(),
		),
		'site_url' => array(
			'label' => 'Site URL',
			'value' => site_url(),
		),
		'wp_lang' => array(
			'label' => 'WP Language',
			'value' => defined( 'WPLANG' ) ? WPLANG : get_option( 'WPLANG' ),
		),
		'wp_version' => array(
			'label' => 'WP Version',
			'value' => get_bloginfo( 'version' ) . ( ( is_multisite() ) ? ' (WPMU)' : '' ),
		),
		'web_server' => array(
			'label' => 'Web Server Info',
			'value' => $_SERVER['SERVER_SOFTWARE'],
		),
		'php_version' => array(
			'label' => 'PHP Version',
			'value' => phpversion(),
		),
		'mysql_version' => array(
			'label' => 'MySQL Version',
			'value' => $wpdb->db_version(),
		),
		'wp_memory_limit' => array(
			'label' => 'WP Memory Limit',
			'value' => WP_MEMORY_LIMIT,
		),
		'wp_debug_mode' => array(
			'label' => 'WP Debug Mode',
			'value' => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No',
		),
		'wp_max_upload_size' => array(
			'label' => 'WP Max Upload Size',
			'value' => size_format( wp_max_upload_size() ),
		),
		'php_post_max_size' => array(
			'label' => 'PHP Post Max Size',
			'value' => ini_get( 'post_max_size' ),
		),
		'php_time_limit' => array(
			'label' => 'PHP Time Limit',
			'value' => ini_get( 'max_execution_time' ),
		),
		'php_safe_mode' => array(
			'label' => 'PHP Safe Mode',
			'value' => ( ini_get( 'safe_mode' ) ) ? 'Yes' : 'No',
		),
		'user_agent' => array(
			'label' => 'User Agent',
			'value' => $_SERVER['HTTP_USER_AGENT'],
		),
		'audiotheme_version' => array(
			'label' => 'AudioTheme Version',
			'value' => AUDIOTHEME_VERSION,
		),
		'theme' => array(
			'label' => 'Theme',
			'value' => $theme->get( 'Name' ),
		),
		'theme_version' => array(
			'label' => 'Theme Version',
			'value' => $theme->get( 'Version' ),
		),
	);

	if ( get_template() !== get_stylesheet() ) {
		$theme = wp_get_theme();

		$data['child_theme'] = array(
			'label' => 'Child Theme',
			'value' => $theme->get( 'Name' ),
		);

		$data['child_theme_version'] = array(
			'label' => 'Child Theme',
			'value' => $theme->get( 'Version' ),
		);
	}

	if ( 'plaintext' === $args['format'] ) {
		$plain = '';

		foreach ( $data as $key => $info ) {
			$plain .= $info['label'] . ': ' . $info['value'] . "\n";
		}

		$data = trim( $plain );
	}

	return $data;
}
