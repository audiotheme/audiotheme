<?php
/**
 * AudioTheme framework dashboard.
 *
 * Consists of the top-level AudioTheme menu and the various submenus.
 *
 * @package AudioTheme_Framework
 * @subpackage Administration
 */

/**
 * Setup the framework dashboard.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_init() {
	add_action( 'audiotheme_register_settings', 'audiotheme_dashboard_register_settings' );
	add_action( 'admin_menu', 'audiotheme_dashboard_admin_menu' );
	add_action( 'admin_init', 'audiotheme_dashboard_sort_menu' );

	add_action( 'wp_ajax_audiotheme_ajax_activate_license', 'audiotheme_ajax_activate_license' );

	add_action( 'audiotheme_update_response_error', 'audiotheme_license_clear_status' );
	add_action( 'update_option_audiotheme_license_key', 'audiotheme_license_key_option_update', 10, 2 );

	add_action( 'load-audiotheme_page_audiotheme-settings', 'audiotheme_dashboard_settings_help' );
}

/**
 * Build the framework admin menu.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_admin_menu() {
	$pagehook = add_menu_page(
		__( 'AudioTheme', 'audiotheme-i18n' ),
		__( 'AudioTheme', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme',
		'audiotheme_dashboard_features_screen',
		null,
		3.901
	);

	add_submenu_page(
		'audiotheme',
		__( 'Features', 'audiotheme-i18n' ),
		__( 'Features', 'audiotheme-i18n' ),
		'manage_options',
		'audiotheme',
		'audiotheme_dashboard_features_screen'
	);
}

/**
 * Register default global settings.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_register_settings() {
	$screen = add_audiotheme_settings_screen( 'audiotheme-settings', __( 'Settings', 'audiotheme-i18n' ), array(
		'menu_title'   => __( 'Settings', 'audiotheme-i18n' ),
		'option_group' => 'audiotheme_options',
		'option_name'  => array( 'audiotheme_options', 'audiotheme_license_key', 'audiotheme_disable_directory_browsing' ),
		'show_in_menu' => 'audiotheme',
		'capability'   => 'manage_options'
	) );

	$screen->add_section( 'directory_browsing', __( 'Directory Browsing', 'audiotheme-i18n' ), array(
		'priority' => 50,
	) );

		$screen->add_field( 'audiotheme_disable_directory_browsing', __( 'Directory Browsing', 'audiotheme-i18n' ), 'checkbox', array(
			'option_name' => 'audiotheme_disable_directory_browsing',
			'choices'     => array(
				'1' => 'Disable directory browsing?',
			),
		) );

	$screen->add_section( 'license', __( 'License', 'audiotheme-i18n' ), array(
		'priority' => 0,
		'callback' => 'audiotheme_dashboard_settings_license_section',
	) );

		$screen->add_field( 'audiotheme_license_key', __( 'License Key', 'audiotheme-i18n' ), 'audiotheme_dashboard_license_input', array(
			'option_name' => 'audiotheme_license_key',
		) );


	// System Info Tab

	$screen->add_tab( 'system_info', __( 'System', 'audiotheme-i18n' ) );

		$screen->add_section( 'system_info', '', array(
			'callback' => 'audiotheme_dashboard_settings_system_section',
		) );

		$screen->add_field( 'system_info', __( 'Export Data', 'audiotheme-i18n' ), 'html', array(
			'label'  => '',
			'output' => '<textarea id="audiotheme-system-info-export" class="widefat">' . audiotheme_system_info( array( 'format' => 'plaintext' ) ) . '</textarea>',
		) );
}

/**
 * Display the system data tables.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_settings_system_section() {
	$data = audiotheme_system_info();

	$sections = array(
		array(
			'section' => __( 'AudioTheme', 'audiotheme-i18n' ),
			'keys'    => array( 'audiotheme_version', 'theme', 'theme_version', 'child_theme', 'child_theme_version' ),
		),
		array(
			'section' => __( 'WordPress', 'audiotheme-i18n' ),
			'keys'    => array( 'home_url', 'site_url', 'wp_version', 'wp_lang', 'wp_memory_limit', 'wp_debug_mode', 'wp_max_upload_size' ),
		),
		array(
			'section' => __( 'Environment', 'audiotheme-i18n' ),
			'keys'    => array( 'web_server', 'php_version', 'mysql_version', 'php_post_max_size', 'php_time_limit' ),
		),
		array(
			'section' => __( 'Browser', 'audiotheme-i18n' ),
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
 */
function audiotheme_dashboard_features_screen() {
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-features.php' );
}

/**
 * Display the dashboard help screen.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_help_screen() {
	include( AUDIOTHEME_DIR . 'admin/views/dashboard-help.php' );
}

/**
 * Sort the admin menu.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_sort_menu() {
	global $menu;

	if ( $menu ) {
		$menu = array_values( $menu ); // Re-key the array.

		$separator = array( '', 'read', 'separator-before-audiotheme', '', 'wp-menu-separator' );
		audiotheme_menu_insert_item( $separator, 'audiotheme', 'before' );

		audiotheme_menu_move_item( 'audiotheme-gigs', 'audiotheme' );
		audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_record', 'audiotheme-gigs' );
		audiotheme_menu_move_item( 'edit.php?post_type=audiotheme_video', 'edit.php?post_type=audiotheme_record' );

		audiotheme_submenu_move_after( 'audiotheme-settings', 'audiotheme', 'audiotheme' );
	}
}

/**
 * License section description callback.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_settings_license_section( $section ) {
	echo sprintf( __( 'Find your license key in <a href="%s" target="_blank">your account</a> on AudioTheme.com. Your license key allows you to recieve automatic upgrades and support.', 'audiotheme-i18n' ), 'http://audiotheme.com/account/' );
}

/**
 * A custom callback to display the field for entering and activating a license key.
 *
 * @since 1.0.0
 *
 * @param array $args Array of arguments to modify output.
 */
function audiotheme_dashboard_license_input( $args ) {
	extract( $args );

	$value = get_audiotheme_option( $option_name, $key, $default  );
	$status = get_option( 'audiotheme_license_status' );
	$activated_response = ' <strong class="audiotheme-response is-valid">' . esc_js( __( 'Activated!', 'audiotheme-i18n' ) ) . '</strong>';

	printf( '<input type="text" name="%s" id="%s" value="%s" class="audiotheme-settings-license-text audiotheme-settings-text regular-text">',
		esc_attr( $field_name ),
		esc_attr( $field_id ),
		esc_attr( $value )
	);

	if ( ! isset( $status->status ) || 'ok' != $status->status ) {
		echo '<input type="button" value="' . __( 'Activate', 'audiotheme-i18n' ) . '" disabled="disabled" class="audiotheme-settings-license-button button button-primary">';
		audiotheme_admin_spinner();
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

			$spinner.show();

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
						errors['empty']         = '<?php echo esc_js( __( 'Empty license key.', 'audiotheme-i18n' ) ); ?>';
						errors['invalid']       = '<?php echo esc_js( __( 'Invalid license key.', 'audiotheme-i18n' ) ); ?>';
						errors['expired']       = '<?php echo esc_js( __( 'License key expired.', 'audiotheme-i18n' ) ) .' <a href="http://audiotheme.com/view/audiotheme/" target="_blank">' . esc_js( __( 'Renew now.', 'audiotheme-i18n' ) ) . '</a>'; ?>';
						errors['limit_reached'] = '<?php echo esc_js( __( 'Activation limit reached.', 'audiotheme-i18n' ) ) . ' <a href="http://audiotheme.com/view/audiotheme/" target="_blank">' . esc_js( __( 'Upgrade your license.', 'audiotheme-i18n' ) ) . '</a>'; ?>';

						if ( 'status' in data && data.status in errors ) {
							$response.html( errors[ data.status ] );
						} else {
							$response.html( '<?php echo esc_js( __( 'Oops, there was an error.', 'audiotheme-i18n' ) ); ?>' );
						}
					}

					$spinner.hide();
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
 */
function audiotheme_ajax_activate_license() {
	if ( isset( $_POST['license'] ) && wp_verify_nonce( $_POST['nonce'], 'audiotheme-activate-license' ) ) {
		update_option( 'audiotheme_license_key', $_POST['license'] );

		$updater = new Audiotheme_Updater( array( 'api_url'  => 'http://127.0.0.1/woocommerce/api/' ) );
		$response = $updater->activate_license( $_POST['license'] );

		update_option( 'audiotheme_license_status', $response );

		if ( isset( $response->status ) && 'ok' == $response->status ) {
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
 *
 * @param object $response Update response.
 */
function audiotheme_license_clear_status( $response ) {
	$license_errors = array( 'empty_license', 'invalid_license', 'not_activated', 'expired_license' );

	if ( ! isset( $response->status ) || in_array( $response->status, $license_errors ) ) {
		update_option( 'audiotheme_license_status', '' );
	}
}

/**
 * Add a help tab to the dashboard settings screen.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_settings_help() {
	$content  = '<h4>' . __( 'For more information:', 'audiotheme-i18n' ) . '</h4>';
	$content .= '<p><a href="http://audiotheme.com/account/" target="_blank">' . __( "Find Your License", 'audiotheme-i18n' ) . '</a></p>';
	$content .= '<p><a href="http://audiotheme.com/view/audiotheme/" target="_blank">' . __( "Upgrade Your License", 'audiotheme-i18n' ) . '</a></p>';
	$content .= '<p><a href="http://audiotheme.com/support/" target="_blank">' . __( "Support Forum", 'audiotheme-i18n' ) . '</a></p>';
	get_current_screen()->set_help_sidebar( $content );

	get_current_screen()->add_help_tab( array(
		'id'      => 'settings',
		'title'   => __( 'Settings', 'audiotheme-i18n' ),
		'content' =>
			'<h4>' . __( 'License Key', 'audiotheme-i18n' ) . '</h4>' .
			'<p>' . __( "Your license key allows you to receive automatic updates and support for as long as your license is active. Don't worry, you won't lose any features you're currently using if it expires, however, it's a good idea to keep your license active to gain access to security upgrades, bug fixes, new features, or just to ask a question if you need a little help.", 'audiotheme-i18n' ) . '</p>' .
			'<p>' . sprintf( __( "Your license key can be found in <a href='%s' target='_blank'>your account</a> on AudioTheme.com or in your purchase email. It's a random string consisting of 32 numbers or letters. Let us know if you need help finding it.", 'audiotheme-i18n' ), 'http://audiotheme.com/account/' ) . '</p>' .
			'<p><em>' . __( "<strong>Example key:</strong>", 'audiotheme-i18n' ) . ' <code>a72fdacfb04efa15976ed843d0bc7fec</code></em></p>' .
			'<p>' . __( "Simply copy and paste it into the <strong>License Key</strong> field and click the <strong>Activate</strong> button. If your license is valid, that's all you have to do.", 'audiotheme-i18n' ) . '</p>' .
			'<p><em>' . sprintf( __( "If your license has expired or you want to use AudioTheme on more than one site, visit <a href='%s' target='_blank'>AudioTheme.com</a> to upgrade.", 'audiotheme-i18n' ), 'http://audiotheme.com/view/audiotheme/' ) . '</em></p>' .

			'<h4>' . __( 'Directory Browsing', 'audiotheme-i18n' ) . '</h4>' .
			'<p>' . __( "Most servers running WordPress have a feature referred to as directory browsing. When active, visitors can see a list of files in some folders on your server if they know where to look. These folders might contain music or pictures that you don't want to be easily accessed. While anything you make available online can't be completely protected, disabling directory browsing will help hide important files from prying eyes.", 'audiotheme-i18n' ) . '</p>',
	) );

	get_current_screen()->add_help_tab( array(
		'id'      => 'system',
		'title'   => __( 'System', 'audiotheme-i18n' ),
		'content' =>
			'<p>' . __( "The <strong>System</strong> tab displays information about your system that may be useful for support requests.", 'audiotheme-i18n' ) . '</p>' .
			'<p>' . __( "At the bottom of the screen is a field to copy the information displayed, so you can send it along with a support request if needed.", 'audiotheme-i18n' ) . '</p>',
	) );
}
