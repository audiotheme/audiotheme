<?php
/**
 * AudioTheme framework dashboard.
 *
 * Consists of the top-level AudioTheme menu and the various submenus.
 *
 * @package AudioTheme_Framework
 * @subpackage Administration
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
	$pagehook = add_menu_page(
		__( 'AudioTheme', 'audiotheme' ),
		__( 'AudioTheme', 'audiotheme' ),
		'edit_posts',
		'audiotheme',
		'audiotheme_dashboard_features_screen',
		audiotheme_encode_svg( 'admin/images/dashicons/audiotheme.svg' ),
		511
	);

	add_submenu_page(
		'audiotheme',
		__( 'Features', 'audiotheme' ),
		__( 'Features', 'audiotheme' ),
		'edit_posts',
		'audiotheme',
		'audiotheme_dashboard_features_screen'
	);
}

/**
 * Register default global settings.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_register_settings() {
	$screen = add_audiotheme_settings_screen( 'audiotheme-settings', __( 'Settings', 'audiotheme' ), array(
		'menu_title'   => ( is_network_admin() ) ? __( 'AudioTheme', 'audiotheme' ) : __( 'Settings', 'audiotheme' ),
		'option_group' => 'audiotheme_options',
		'option_name'  => array( 'audiotheme_options', 'audiotheme_license_key' ),
		'show_in_menu' => ( is_network_admin() ) ? 'settings.php' : 'audiotheme',
		'capability'   => ( is_network_admin() ) ? 'manage_network_options' : 'manage_options',
	) );

	if ( is_multisite() && ! is_network_admin() ) {
		return;
	}

	$screen->add_section( 'license', __( 'License', 'audiotheme' ), array(
		'priority' => 0,
		'callback' => 'audiotheme_dashboard_settings_license_section',
	) );

		$screen->add_field( 'audiotheme_license_key', __( 'License Key', 'audiotheme' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_license_key',
		) );

		// System Info Tab

		$screen->add_tab( 'system_info', __( 'System', 'audiotheme' ) );

		$screen->add_section( 'system_info', '', array(
			'callback' => 'audiotheme_dashboard_settings_system_section',
		) );

		$screen->add_field( 'system_info', __( 'Export Data', 'audiotheme' ), 'html', array(
			'label'  => '',
			'output' => '<textarea id="audiotheme-system-info-export" class="widefat">' . audiotheme_system_info( array( 'format' => 'plaintext' ) ) . '</textarea>',
		) );
}

/**
 * Manually save network settings.
 *
 * @since 1.3.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_save_network_settings() {
	// Just return since other network settings screens will use the same action.
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'audiotheme_options-options' ) ) {
		return;
	}

	// Update the license key.
	update_option( 'audiotheme_license_key', ( empty( $_POST['audiotheme_license_key'] ) ) ? '' : esc_html( $_POST['audiotheme_license_key'] ) );

	$redirect = add_query_arg( 'page', 'audiotheme-settings', admin_url( 'network/settings.php' ) );
	wp_safe_redirect( esc_url_raw( $redirect ) );
	exit;
}

/**
 * Display the system data tables.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_settings_system_section() {
	$data = audiotheme_system_info();

	$sections = array(
		array(
			'section' => __( 'AudioTheme', 'audiotheme' ),
			'keys'    => array( 'audiotheme_version', 'theme', 'theme_version', 'child_theme', 'child_theme_version' ),
		),
		array(
			'section' => __( 'WordPress', 'audiotheme' ),
			'keys'    => array( 'home_url', 'site_url', 'wp_version', 'wp_lang', 'wp_memory_limit', 'wp_debug_mode', 'wp_max_upload_size' ),
		),
		array(
			'section' => __( 'Environment', 'audiotheme' ),
			'keys'    => array( 'web_server', 'php_version', 'mysql_version', 'php_post_max_size', 'php_time_limit', 'php_safe_mode' ),
		),
		array(
			'section' => __( 'Browser', 'audiotheme' ),
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
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-features.php' );
}

/**
 * Sort the admin menu.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_sort_menu() {
	global $menu;

	if ( ! is_network_admin() && $menu ) {
		$menu = array_values( $menu ); // Re-key the array.

		audiotheme_menu_move_item( 'audiotheme', 'separator1', 'before' );

		$separator = array( '', 'read', 'separator-before-audiotheme', '', 'wp-menu-separator' );
		audiotheme_menu_insert_item( $separator, 'audiotheme', 'before' );

		// Reverse the order and always insert them after the main AudioTheme menu item.
		audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_video', 'audiotheme' );
		audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_record', 'audiotheme' );
		audiotheme_menu_move_item( 'audiotheme-gigs', 'audiotheme' );

		audiotheme_submenu_move_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
	}
}

/**
 * License section description callback.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_dashboard_settings_license_section( $section ) {
	echo sprintf( __( 'Find your license key in <a href="%s" target="_blank">your account</a> on AudioTheme.com. Your license key allows you to recieve automatic upgrades and support.', 'audiotheme' ), 'https://audiotheme.com/account/' );
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
	extract( $args );

	$value = get_audiotheme_option( $option_name, $key, $default );
	$status = get_option( 'audiotheme_license_status' );
	$activated_response = ' <strong class="audiotheme-response is-valid">' . esc_js( __( 'Activated!', 'audiotheme' ) ) . '</strong>';

	printf( '<input type="text" name="%s" id="%s" value="%s" class="audiotheme-settings-license-text audiotheme-settings-text regular-text">',
		esc_attr( $field_name ),
		esc_attr( $field_id ),
		esc_attr( $value )
	);

	if ( ! isset( $status->status ) || 'ok' !== $status->status ) {
		echo '<input type="button" value="' . __( 'Activate', 'audiotheme' ) . '" disabled="disabled" class="audiotheme-settings-license-button button button-primary">';
		audiotheme_admin_spinner( array( 'class' => 'audiotheme-license-spinner' ) );
		echo '<br><span class="audiotheme-response"></span>';
	} else {
		echo $activated_response;
	}
	?>
	<script type="text/javascript">
	jQuery(function($) {
		var $field = $('#audiotheme_license_key'),
			$button = $field.parent().find('.button');
			$spinner = $field.parent().find('.spinner');

		$field.on('keyup', function() {
			if ( '' != $field.val() ) {
				$button.attr('disabled', false);
			} else {
				$button.attr('disabled', true);
			}
		}).trigger('keyup');

		$button.on('click', function(e) {
			e.preventDefault();

			$spinner.addClass('is-visible');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'audiotheme_ajax_activate_license',
					license: $field.val(),
					nonce: '<?php echo wp_create_nonce( 'audiotheme-activate-license' ); ?>'
				},
				dataType: 'json',
				success: function( data ) {
					data = data || {};

					if ( 'status' in data && 'ok' == data.status ) {
						$field.parent().find('.audiotheme-response').remove();
						$button.hide().after( '<?php echo $activated_response; ?>' );
					} else {
						var $response = $field.parent().find('.audiotheme-response').addClass('is-error'),
							errors = [];

						// ok|empty|unknown|invalid|expired|limit_reached|failed
						errors['empty']         = '<?php echo esc_js( __( 'Empty license key.', 'audiotheme' ) ); ?>';
						errors['invalid']       = '<?php echo esc_js( __( 'Invalid license key.', 'audiotheme' ) ); ?>';
						errors['expired']       = '<?php echo esc_js( __( 'License key expired.', 'audiotheme' ) ) .' <a href="https://audiotheme.com/view/audiotheme/" target="_blank">' . esc_js( __( 'Renew now.', 'audiotheme' ) ) . '</a>'; ?>';
						errors['limit_reached'] = '<?php echo esc_js( __( 'Activation limit reached.', 'audiotheme' ) ) . ' <a href="https://audiotheme.com/view/audiotheme/" target="_blank">' . esc_js( __( 'Upgrade your license.', 'audiotheme' ) ) . '</a>'; ?>';

						if ( 'status' in data && data.status in errors ) {
							$response.html( errors[ data.status ] );
						} else {
							$response.html( '<?php echo esc_js( __( 'Oops, there was an error.', 'audiotheme' ) ); ?>' );
						}
					}

					$spinner.removeClass('is-visible');
				}
			});
		});
	});
	</script>
	<?php
}

/**
 * Send a request to the remote API to activate the license for the current
 * site.
 *
 * @since 1.0.0
 * @deprecated 1.9.0
 */
function audiotheme_ajax_activate_license() {
	if ( isset( $_POST['license'] ) && wp_verify_nonce( $_POST['nonce'], 'audiotheme-activate-license' ) ) {
		update_option( 'audiotheme_license_key', $_POST['license'] );

		$updater = new Audiotheme_Updater();
		$response = $updater->activate_license( $_POST['license'] );

		update_option( 'audiotheme_license_status', $response );

		if ( isset( $response->status ) && 'ok' === $response->status ) {
			// @todo Clear the last update status check with a 'not_activated' response.
			update_option( 'audiotheme_license_key', $_POST['license'] );
		}

		wp_send_json( $response );
	}
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
	update_option( 'audiotheme_license_status', '' );
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
	$license_errors = array( 'empty_license', 'invalid_license', 'not_activated', 'expired_license' );

	if ( ! isset( $response->status ) || in_array( $response->status, $license_errors ) ) {
		update_option( 'audiotheme_license_status', '' );
	}
}
