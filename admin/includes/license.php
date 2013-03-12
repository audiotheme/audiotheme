<?php
/**
 * License section description callback.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_settings_license_section( $section ) {
	echo __( 'Find your license key in your account on AudioTheme.com. Your license key is used for automatic upgrades and support.', 'audiotheme-i18n' );
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
						errors['expired']       = '<?php echo esc_js( __( 'License key expired.', 'audiotheme-i18n' ) ) .' <a href="http://audiotheme.com/">' . esc_js( __( 'Renew now.', 'audiotheme-i18n' ) ) . '</a>'; ?>';
						errors['limit_reached'] = '<?php echo esc_js( __( 'Activation limit reached.', 'audiotheme-i18n' ) ) . ' <a href="http://audiotheme.com/">' . esc_js( __( 'Upgrade your license.', 'audiotheme-i18n' ) ) . '</a>'; ?>';

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
