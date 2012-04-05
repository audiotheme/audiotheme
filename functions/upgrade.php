<?php
/**
 * Handles audiotheme upgrades.
 *
 * @package audiotheme
 */

/**
 * This function pings an http://api.audiothemetheme.com/ asking if a new
 * version of this theme is available. If not, it returns FALSE.
 * If so, the external server passes serialized data back to this
 * function, which gets unserialized and returned for use.
 *
 * @since 1.0
 */
function audiotheme_update_check() {
	global $wp_version;

	//	If updates are disabled
	if( ! audiotheme_get_option( 'update' ) || ! current_theme_supports( 'audiotheme-auto-updates' ) )
		return FALSE;

	$audiotheme_update = get_transient( 'audiotheme-update' );

	if( !$audiotheme_update ) {
		$url = 'http://api.audiotheme.com/update-framework/';
		$options = array( 
			'body' => array( 
				'audiotheme_version' => AUIDIOTHEME_VERSION,
				'wp_version' => $wp_version,
				'php_version' => phpversion(),
				'uri' => home_url(),
				'user-agent' => "WordPress/$wp_version;"
			 )
		 );

		$response = wp_remote_post( $url, $options );
		$audiotheme_update = wp_remote_retrieve_body( $response );

		// If an error occurred, return FALSE, store for 1 hour
		if( $audiotheme_update == 'error' || is_wp_error( $audiotheme_update ) || !is_serialized( $audiotheme_update ) ) {
			set_transient( 'audiotheme-update', array( 'new_version' => AUIDIOTHEME_VERSION ), 60*60 ); // store for 1 hour
			return FALSE;
		}

		// Else, unserialize
		$audiotheme_update = maybe_unserialize( $audiotheme_update );

		// And store in transient
		set_transient( 'audiotheme-update', $audiotheme_update, 60*60*24 ); // store for 24 hours
	}

	// If we're already using the latest version, return FALSE
	if( version_compare( AUIDIOTHEME_VERSION, $audiotheme_update['new_version'], '>=' ) )
		return FALSE;

	return $audiotheme_update;
}


/**
 * Upgrade the database to version 1703
 *
 * @since 1.0
 */
function audiotheme_upgrade_1703() {

	/** Update Settings */
	_audiotheme_update_settings( array( 
		'theme_version' => '1.0',
		'db_version' => '1703'
	 ) );

}


add_action( 'admin_init', 'audiotheme_upgrade' );
/**
 * This iterative upgrade function will take a audiotheme installation,
 * no matter how old, and upgrade its options to the latest version.
 *
 * It used to iterate over theme version, but now uses a database
 * version system, which allows for changes within pre-releases, too.
 *
 * @since 1.0
 */
function audiotheme_upgrade() {

	// Don't do anything if we're on the latest version
	if( audiotheme_get_option( 'db_version' ) >= PARENT_DB_VERSION )
		return;

	#########################
#	UPGRADE TO VERSION 1.0.1
	#########################

	// Check to see if we need to upgrade to 1.0.1
	if( version_compare( audiotheme_get_option( 'theme_version' ), '1.0.1', '<' ) ) {

		$theme_settings = get_option( audiotheme_SETTINGS_FIELD );
		$new_settings = array( 
			'nav_home' => 1,
			'nav_twitter_text' => 'Follow me on Twitter',
			'subnav_home' => 1,
			'theme_version' => '1.0.1'
		 );

		$settings = wp_parse_args( $new_settings, $theme_settings );
		update_option( audiotheme_SETTINGS_FIELD, $settings );

	}
		
	##########################
#	UPGRADE DB TO VERSION 1703
	##########################
	if( audiotheme_get_option( 'db_version' ) < '1703' ) {
		audiotheme_upgrade_1703();
	}

	do_action( 'audiotheme_upgrade' );

}


add_action( 'audiotheme_upgrade', 'audiotheme_upgrade_redirect' );
/**
 * This function will redirect the user back to the theme settings page,
 * refreshing the data and alerting the user that they have successfully updated.
 *
 * @since 1.6
 */
function audiotheme_upgrade_redirect() {

	if( ! is_admin() ) return;

	audiotheme_admin_redirect( 'audiotheme', array( 'upgraded' => 'true' ) );
	exit;

}


add_action( 'admin_notices', 'audiotheme_upgraded_notice' );
/**
 * Upgrade Notice
 *
 * This displays the notice to the user that their theme settings were
 * successfully upgraded to the latest version.
 *
 * @since 1.0
 */
function audiotheme_upgraded_notice() {

	if( !isset( $_REQUEST['page'] ) || $_REQUEST['page'] != 'audiotheme' )
		return;

	if( isset( $_REQUEST['upgraded'] ) && $_REQUEST['upgraded'] == 'true' ) {
		echo '<div id="message" class="updated highlight" id="message"><p><strong>'.sprintf( __( 'Congratulations! You are now rocking AudioTheme %s', 'audiotheme' ), audiotheme_get_option( 'theme_version' ) ).'</strong></p></div>';
	}

}


add_filter( 'update_theme_complete_actions', 'audiotheme_update_action_links', 10, 2 );
/**
 * Update Action Links
 *
 * Filters the action links at the end of an upgrade
 *
 * This function filters the action links that are presented to the
 * user at the end of a theme update. If the theme being updated is
 * not audiotheme, the filter returns the default values. Otherwise,
 * it will provide a link to the audiotheme Theme Settings page, which
 * will trigger the database/settings upgrade.
 *
 * @since 1.0
 */
function audiotheme_update_action_links( $actions, $theme ) {

	if( $theme != 'audiotheme' )
		return $actions;

	return sprintf( '<a href="%s">%s</a>', menu_page_url( 'audiotheme', 0 ), __( 'Click here to complete the upgrade', 'audiotheme' ) );

}


add_action( 'admin_notices', 'audiotheme_update_nag' );
/**
 * Update Nag
 *
 * This function displays the update nag at the top of the
 * dashboard if there is an audiotheme update available.
 *
 * @since 1.0
 */
function audiotheme_update_nag() {
	$audiotheme_update = audiotheme_update_check();

	if( !is_super_admin() || !$audiotheme_update )
		return false;

	$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=audiotheme', 'upgrade-theme_audiotheme' );
	$update_onclick = __( 'Upgrading audiotheme will overwrite the current installed version of audiotheme. Are you sure you want to upgrade?. "Cancel" to stop, "OK" to upgrade.', 'audiotheme' );

	echo '<div id="update-nag">';
	printf( __( 'audiotheme %s is available. <a href="%s" class="thickbox thickbox-preview">Check out what\'s new</a> or <a href="%s" onclick="return audiotheme_confirm( \'%s\' );">update now</a>.', 'audiotheme' ), esc_html( $audiotheme_update['new_version'] ), esc_url( $audiotheme_update['changelog_url'] ), $update_url, esc_js( $update_onclick ) );
	echo '</div>';
}


add_action( 'init', 'audiotheme_update_email' );
/**
 * Update Email
 *
 * This function does several checks before finally sending out
 * a notification email to the specified email address, alerting
 * it to a audiotheme update available for that install.
 *
 * @since 1.0
 */
function audiotheme_update_email() {

	// Pull email options from DB
	$email_on = audiotheme_get_option( 'update_email' );
	$email = audiotheme_get_option( 'update_email_address' );

	// If we're not supposed to send an email, or email is blank/invalid, stop!
	if( !$email_on || !is_email( $email ) )
		return;

	// Check for updates
	$update_check = audiotheme_update_check();

	// If no new version is available, stop!
	if( !$update_check )
		return;

	// If we've already sent an email for this version, stop!
	if( get_option( 'audiotheme-update-email' ) == $update_check['new_version'] )
		return;

	// Let's send an email!
	$subject = sprintf( __( 'audiotheme %s is available for %s', 'audiotheme' ), esc_html( $update_check['new_version'] ), home_url() );
	$message = sprintf( __( 'audiotheme %s is now available. We have provided 1-click updates for this theme, so please log into your dashboard and update at your earliest convenience.', 'audiotheme' ), esc_html( $update_check['new_version'] ) );
	$message .= "\n\n" . wp_login_url();

	// Update the option so we don't send emails on every pageload!
	update_option( 'audiotheme-update-email', $update_check['new_version'], TRUE );

	// send that puppy!
	wp_mail( sanitize_email( $email ), $subject, $message );

}


add_filter( 'site_transient_update_themes', 'audiotheme_update_push' );
add_filter( 'transient_update_themes', 'audiotheme_update_push' );
/**
 * Update Push
 *
 * This function filters the value that is returned when
 * WordPress tries to pull theme update transient data. It uses
 * audiotheme_update_check() to check to see if we need to do an
 * update, and if so, adds the proper array to the $value->response
 * object. WordPress handles the rest.
 *
 * @since 1.0
 */
function audiotheme_update_push( $value ) {

	$audiotheme_update = audiotheme_update_check();

	if( $audiotheme_update ) {
		$value->response['audiotheme'] = $audiotheme_update;
	}

	return $value;

}


add_action( 'load-update.php', 'audiotheme_flush_update_transient' );
add_action( 'load-themes.php', 'audiotheme_flush_update_transient' );
/**
 * Flush Update Transient
 *
 * This function clears out the audiotheme update transient data
 * so that the server will do a fresh version check when the
 * update is complete, or when the user loads certain admin pages.
 *
 * It also disables the update nag on those pages, as well.
 *
 * @since 1.0
 */
function audiotheme_flush_update_transient() {

	delete_transient( 'audiotheme-update' );
	remove_action( 'admin_notices', 'audiotheme_update_nag' );

}


/**
 * This function takes an array of new settings, merges them with the old settings,
 * and pushes them into the database via update_option().
 *
 * @since 1.7
 */
function _audiotheme_update_settings( $new = '', $setting = audiotheme_SETTINGS_FIELD ) {
	update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
}