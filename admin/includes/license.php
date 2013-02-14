<?php
/**
 * License section description callback.
 *
 * @since 1.0.0
 */
function audiotheme_dashboard_settings_license_section( $section ) {
	echo 'Find your license in your account on AudioTheme.com. Your license key is used for automatic upgrades and support.';
}

/**
 * A custom callback to display the field for entering a license key.
 *
 * @since 1.0.0
 *
 * @todo Custom field callback for license key fields to activate and validate
 *       the keys without doing a post back. Should add elegant error reporting.
 * @todo Pass an arg that lets this routine determine whether or not a button
 *       should be output or a valid status message.
 *
 * @param array $args Array of arguments to modify output.
 */
function audiotheme_dashboard_license_input( $args ) {
	extract( $args );

	$value = get_audiotheme_option( $option_name, $key, $default  );
	$status = get_option( 'audiotheme_license_status' );

	printf( '<input type="text" name="%s" id="%s" value="%s" class="audiotheme-settings-license-text audiotheme-settings-text regular-text">',
		esc_attr( $field_name ),
		esc_attr( $field_id ),
		esc_attr( $value )
	);

	if ( ! isset( $status->status ) || 'ok' != $status->status ) {
		echo '<input type="button" value="Check" class="audiotheme-settings-license-button button button-primary">';
		echo '<br><span class="response"></span>';
	} else {
		echo ' Good!';
	}
	?>
	<script type="text/javascript">
	jQuery(function($) {
		var $field = $('#audiotheme_license'),
			$button = $field.parent().find('.button');

		$button.on('click', function(e) {
			e.preventDefault();

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'audiotheme_ajax_activate_license',
					license: $field.val()
				},
				dataType: 'json',
				success: function( data ) {
					data = data || {};

					if ( 'ok' == data.status ) {
						$button.hide().after('Good!');
					} else {
						var $response = $field.parent().find('.response').addClass('error'),
							errors = [];

						// ok|empty|unknown|invalid|expired|limit_reached|failed
						errors['empty']         = '<?php echo esc_js( __( 'Empty license key.', 'audiotheme-i18n' ) ); ?>';
						errors['invalid']       = '<?php echo esc_js( __( 'Invalid license key.', 'audiotheme-i18n' ) ); ?>';
						errors['expired']       = '<?php echo esc_js( __( 'License key expired. <a href="http://audiotheme.com/">Renew now.</a>', 'audiotheme-i18n' ) ); ?>';
						errors['limit_reached'] = '<?php echo esc_js( __( 'Activation limit reached. <a href="http://audiotheme.com/">Upgrade your license.</a>', 'audiotheme-i18n' ) ); ?>';

						if ( data.status in errors ) {
							$response.html( errors[ data.status ] );
						} else {
							$response.html( '<?php echo esc_js( __( 'Oops, there was an error.', 'audiotheme-i18n' ) ); ?>' );
						}
					}
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
 *
 * @todo Update an option that stores the response.
 * @todo Use a nonce.
 */
function audiotheme_ajax_activate_license() {
	if ( isset( $_POST['license'] ) ) {
		update_option( 'audiotheme_license', $_POST['license'] );

		$updater = new Audiotheme_Updater( array( 'api_url'  => 'http://127.0.0.1/woocommerce/api/' ) );
		$response = $updater->activate_license( $_POST['license'] );

		update_option( 'audiotheme_license_status', $response );

		if ( isset( $response->status ) && 'ok' == $response->status ) {
			update_option( 'audiotheme_license', $_POST['license'] );
		}

		wp_send_json( $response );
	}
}

/**
 * Clear the license status option when the license is changed.
 *
 * @since 1.0.0
 *
 * @param array $oldvalue Old option value.
 * @param array $newvalue New option value.
 */
function audiotheme_license_option_update( $oldvalue, $newvalue ) {
	update_option( 'audiotheme_license_status', '' );
}
