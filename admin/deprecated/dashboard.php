<?php
/**
 * AudioTheme framework dashboard.
 *
 * Consists of the top-level AudioTheme menu and the various submenus.
 *
 * @package AudioTheme\Administration
 * @since 1.0.0
 * @deprecated 1.9.0
 */

/**
 * Build the framework admin menu.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_admin_menu() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Register default global settings.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_register_settings() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Manually save network settings.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_save_network_settings() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Display the system data tables.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_settings_system_section() {
	_deprecated_function( __FUNCTION__, '1.9.0' );

	$data = audiotheme_system_info();

	$sections = array(
		array(
			'section' => 'AudioTheme',
			'keys'    => array( 'audiotheme_version', 'theme', 'theme_version', 'child_theme', 'child_theme_version' ),
		),
		array(
			'section' => 'WordPress',
			'keys'    => array( 'home_url', 'site_url', 'wp_version', 'wp_lang', 'wp_memory_limit', 'wp_debug_mode', 'wp_max_upload_size' ),
		),
		array(
			'section' => 'Environment',
			'keys'    => array( 'web_server', 'php_version', 'mysql_version', 'php_post_max_size', 'php_time_limit', 'php_safe_mode' ),
		),
		array(
			'section' => 'Browser',
			'keys'    => array( 'user_agent' ),
		),
	);

	foreach ( $sections as $section ) :
		?>
		<table class="audiotheme-system-info widefat">
			<thead>
				<tr>
					<th colspan="2"><?php echo esc_html( $section['section'] ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $section['keys'] as $key ) {
					if ( isset( $data[ $key ] ) ) {
						printf( '<tr><th scope="row">%s</th><td>%s</td></tr>',
							esc_html( $data[ $key ]['label'] ),
							esc_html( $data[ $key ]['value'] )
						);
					}
				}
				?>
			</tbody>
		</table>
		<?php
	endforeach;
	?>
	<script type="text/javascript">
	jQuery(function($) {
		$('#audiotheme-system-info-export').on('focus', function() {
			$(this).select();
		});
	});
	</script>
	<?php
}

/**
 * Display the main dashboard screen.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_features_screen() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * License section description callback.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_settings_license_section( $section ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * A custom callback to display the field for entering and activating a license key.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $args Array of arguments to modify output.
 */
function audiotheme_dashboard_license_input( $args ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Send a request to the remote API to activate the license for the current
 * site.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_ajax_activate_license() {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Clear the license status option when the key is changed.
 *
 * Forces the new key to be activated.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param array $oldvalue Old option value.
 * @param array $newvalue New option value.
 */
function audiotheme_license_key_option_update( $oldvalue, $newvalue ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}

/**
 * Clear the license status option if an update response was invalid.
 *
 * Forces the license key to be reactivated.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 *
 * @param object $response Update response.
 */
function audiotheme_license_clear_status( $response ) {
	_deprecated_function( __FUNCTION__, '1.9.0' );
}
