<?php
/**
 * Handles Genesis upgrades.
 *
 * @package Genesis
 */

/**
 * This function pings an http://api.genesistheme.com/ asking if a new
 * version of this theme is available. If not, it returns FALSE.
 * If so, the external server passes serialized data back to this
 * function, which gets unserialized and returned for use.
 *
 * @since 1.1
 */
function genesis_update_check() {
	global $wp_version;

	//	If updates are disabled
	if ( !genesis_get_option('update') || !current_theme_supports('genesis-auto-updates') )
		return FALSE;

	$genesis_update = get_transient('genesis-update');

	if ( !$genesis_update ) {
		$url = 'http://api.genesistheme.com/update-themes/';
		$options = array(
			'body' => array(
				'genesis_version' => PARENT_THEME_VERSION,
				'wp_version' => $wp_version,
				'php_version' => phpversion(),
				'uri' => home_url(),
				'user-agent' => "WordPress/$wp_version;"
			)
		);

		$response = wp_remote_post($url, $options);
		$genesis_update = wp_remote_retrieve_body($response);

		// If an error occurred, return FALSE, store for 1 hour
		if ( $genesis_update == 'error' || is_wp_error($genesis_update) || !is_serialized($genesis_update) ) {
			set_transient('genesis-update', array('new_version' => PARENT_THEME_VERSION), 60*60); // store for 1 hour
			return FALSE;
		}

		// Else, unserialize
		$genesis_update = maybe_unserialize($genesis_update);

		// And store in transient
		set_transient('genesis-update', $genesis_update, 60*60*24); // store for 24 hours
	}

	// If we're already using the latest version, return FALSE
	if ( version_compare(PARENT_THEME_VERSION, $genesis_update['new_version'], '>=') )
		return FALSE;

	return $genesis_update;
}

/**
 * Upgrade the database to version 1703
 *
 * @since 1.7.1
 */
function genesis_upgrade_1703() {

	/** Update Settings */
	_genesis_update_settings( array(
		'theme_version' => '1.7.1',
		'db_version' => '1703'
	) );

}

/**
 * Upgrade the database to version 1700
 *
 * @since 1.7
 */
function genesis_upgrade_1700() {
	
	global $wpdb;

	/** Changing the UI. Remove old user options. */
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key = %s OR meta_key = %s", 'meta-box-order_toplevel_page_genesis', 'meta-box-order_genesis_page_seosettings' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->usermeta SET meta_value = %s WHERE meta_key = %s OR meta_key = %s", '1', 'screen_layout_toplevel_page_genesis', 'screen_layout_genesis_page_seosettings' ) );

	/** Update Settings */
	_genesis_update_settings( array(
		'db_version' => '1700'
	) );

}

add_action('admin_init', 'genesis_upgrade');
/**
 * This iterative upgrade function will take a Genesis installation,
 * no matter how old, and upgrade its options to the latest version.
 *
 * It used to iterate over theme version, but now uses a database
 * version system, which allows for changes within pre-releases, too.
 *
 * @since 1.0.1
 */
function genesis_upgrade() {

	// Don't do anything if we're on the latest version
	if ( genesis_get_option( 'db_version' ) >= PARENT_DB_VERSION )
		return;

	#########################
#	UPGRADE TO VERSION 1.0.1
	#########################

	// Check to see if we need to upgrade to 1.0.1
	if ( version_compare(genesis_get_option('theme_version'), '1.0.1', '<') ) {

		$theme_settings = get_option(GENESIS_SETTINGS_FIELD);
		$new_settings = array(
			'nav_home' => 1,
			'nav_twitter_text' => 'Follow me on Twitter',
			'subnav_home' => 1,
			'theme_version' => '1.0.1'
		);

		$settings = wp_parse_args($new_settings, $theme_settings);
		update_option(GENESIS_SETTINGS_FIELD, $settings);

	}

	#########################
#	UPGRADE TO VERSION 1.1
	#########################

	// Check to see if we need to upgrade to 1.1
	if ( version_compare(genesis_get_option('theme_version'), '1.1', '<') ) {

		$theme_settings = get_option(GENESIS_SETTINGS_FIELD);
		$new_settings = array(
			'content_archive_thumbnail' => genesis_get_option('thumbnail'),
			'theme_version' => '1.1'
		);

		$settings = wp_parse_args($new_settings, $theme_settings);
		update_option(GENESIS_SETTINGS_FIELD, $settings);

	}

	#########################
#	UPGRADE TO VERSION 1.1.2
	#########################

	// Check to see if we need to upgrade to 1.1.2
	if ( version_compare(genesis_get_option('theme_version'), '1.1.2', '<') ) {

		$theme_settings = get_option(GENESIS_SETTINGS_FIELD);
		$new_settings = array(
			'header_right' => genesis_get_option('header_full') ? 0 : 1,
			'nav_superfish' => 1,
			'subnav_superfish' => 1,
			'nav_extras_enable' => genesis_get_option('nav_right') ? 1 : 0,
			'nav_extras' => genesis_get_option('nav_right'),
			'nav_extras_twitter_id' => genesis_get_option('twitter_id'),
			'nav_extras_twitter_text' => genesis_get_option('nav_twitter_text'),
			'theme_version' => '1.1.2'
		);

		$settings = wp_parse_args($new_settings, $theme_settings);
		update_option(GENESIS_SETTINGS_FIELD, $settings);

	}

	#########################
#	UPGRADE TO VERSION 1.2
	#########################

	// Check to see if we need to upgrade to 1.2
	if ( version_compare(genesis_get_option('theme_version'), '1.2', '<') ) {

		$theme_settings = get_option(GENESIS_SETTINGS_FIELD);
		$new_settings = array(
			'update' => 1,
			'theme_version' => '1.2'
		);

		$settings = wp_parse_args($new_settings, $theme_settings);
		update_option(GENESIS_SETTINGS_FIELD, $settings);

	}

	#########################
#	UPGRADE TO VERSION 1.3
	#########################

	// Check to see if we need to upgrade to 1.3
	if ( version_compare(genesis_get_option('theme_version'), '1.3', '<') ) {

		//	upgrade theme settings
		$theme_settings = get_option(GENESIS_SETTINGS_FIELD);
		$new_settings = array(
			'author_box_single' => genesis_get_option('author_box'),
			'theme_version' => '1.3'
		);

		$settings = wp_parse_args($new_settings, $theme_settings);
		update_option(GENESIS_SETTINGS_FIELD, $settings);

		//	upgrade SEO settings
		$seo_settings = get_option(GENESIS_SEO_SETTINGS_FIELD);
		$new_settings = array(
			'noindex_cat_archive' => genesis_get_seo_option('index_cat_archive') ? 0 : 1,
			'noindex_tag_archive' => genesis_get_seo_option('index_tag_archive') ? 0 : 1,
			'noindex_author_archive' => genesis_get_seo_option('index_author_archive') ? 0 : 1,
			'noindex_date_archive' => genesis_get_seo_option('index_date_archive') ? 0 : 1,
			'noindex_search_archive' => genesis_get_seo_option('index_search_archive') ? 0 : 1,
			'noodp' => 1,
			'noydir' => 1,
			'canonical_archives' => 1
		);

		$settings = wp_parse_args($new_settings, $seo_settings);
		update_option(GENESIS_SEO_SETTINGS_FIELD, $settings);

		//	delete the store transient, force refresh
		delete_transient('genesis-remote-store');

	}

	#########################
#	UPGRADE TO VERSION 1.6
	#########################

	// Check to see if we need to upgrade to 1.6
	if ( version_compare(genesis_get_option('theme_version'), '1.6', '<') ) {

		/** Vestige nav settings, for backward compatibility */
		if ( 'nav-menu' != genesis_get_option( 'nav_type' ) ) {
			_genesis_vestige( array( 'nav_type', 'nav_superfish', 'nav_home', 'nav_pages_sort', 'nav_categories_sort', 'nav_depth', 'nav_exclude', 'nav_include' ) );
		}

		/** Vestige subnav settings, for backward compatibility */
		if ( 'nav-menu' != genesis_get_option( 'subnav_type' ) ) {
			_genesis_vestige( array( 'subnav_type', 'subnav_superfish', 'subnav_home', 'subnav_pages_sort', 'subnav_categories_sort', 'subnav_depth', 'subnav_exclude', 'subnav_include' ) );
		}

		$theme_settings = get_option(GENESIS_SETTINGS_FIELD);
		$new_settings = array(
			'theme_version' => '1.6'
		);

		$settings = wp_parse_args($new_settings, $theme_settings);
		update_option(GENESIS_SETTINGS_FIELD, $settings);

	}
	
	##########################
#	UPGRADE DB TO VERSION 1700
	##########################
	if ( genesis_get_option( 'db_version' ) < '1700' ) {
		genesis_upgrade_1700();
	}
	
	##########################
#	UPGRADE DB TO VERSION 1703
	##########################
	if ( genesis_get_option( 'db_version' ) < '1703' ) {
		genesis_upgrade_1703();
	}

	do_action( 'genesis_upgrade' );

}

add_action( 'genesis_upgrade', 'genesis_upgrade_redirect' );
/**
 * This function will redirect the user back to the theme settings page,
 * refreshing the data and alerting the user that they have successfully updated.
 *
 * @since 1.6
 */
function genesis_upgrade_redirect() {

	if ( ! is_admin() ) return;

	genesis_admin_redirect( 'genesis', array( 'upgraded' => 'true' ) );
	exit;

}

add_action('admin_notices', 'genesis_upgraded_notice');
/**
 * This displays the notice to the user that their theme settings were
 * successfully upgraded to the latest version.
 *
 */
function genesis_upgraded_notice() {

	if ( !isset($_REQUEST['page']) || $_REQUEST['page'] != 'genesis' )
		return;

	if ( isset($_REQUEST['upgraded']) && $_REQUEST['upgraded'] == 'true') {
		echo '<div id="message" class="updated highlight" id="message"><p><strong>'.sprintf( __('Congratulations! You are now rocking Genesis %s', 'genesis' ), genesis_get_option('theme_version')).'</strong></p></div>';
	}

}

add_filter('update_theme_complete_actions', 'genesis_update_action_links', 10, 2);
/**
 * Filters the action links at the end of an upgrade
 *
 * This function filters the action links that are presented to the
 * user at the end of a theme update. If the theme being updated is
 * not Genesis, the filter returns the default values. Otherwise,
 * it will provide a link to the Genesis Theme Settings page, which
 * will trigger the database/settings upgrade.
 *
 * @since 1.1.3
 *
 */
function genesis_update_action_links($actions, $theme) {

	if ( $theme != 'genesis' )
		return $actions;

	return sprintf( '<a href="%s">%s</a>', menu_page_url( 'genesis', 0 ), __('Click here to complete the upgrade', 'genesis') );

}

add_action('admin_notices', 'genesis_update_nag');
/**
 * This function displays the update nag at the top of the
 * dashboard if there is an Genesis update available.
 *
 * @since 1.1
 */
function genesis_update_nag() {
	$genesis_update = genesis_update_check();

	if ( !is_super_admin() || !$genesis_update )
		return false;

	$update_url = wp_nonce_url('update.php?action=upgrade-theme&amp;theme=genesis', 'upgrade-theme_genesis');
	$update_onclick = __('Upgrading Genesis will overwrite the current installed version of Genesis. Are you sure you want to upgrade?. "Cancel" to stop, "OK" to upgrade.', 'genesis');

	echo '<div id="update-nag">';
	printf( __('Genesis %s is available. <a href="%s" class="thickbox thickbox-preview">Check out what\'s new</a> or <a href="%s" onclick="return genesis_confirm(\'%s\');">update now</a>.', 'genesis'), esc_html( $genesis_update['new_version'] ), esc_url( $genesis_update['changelog_url'] ), $update_url, esc_js( $update_onclick ) );
	echo '</div>';
}

add_action('init', 'genesis_update_email');
/**
 * This function does several checks before finally sending out
 * a notification email to the specified email address, alerting
 * it to a Genesis update available for that install.
 *
 * @since 1.1
 */
function genesis_update_email() {

	// Pull email options from DB
	$email_on = genesis_get_option('update_email');
	$email = genesis_get_option('update_email_address');

	// If we're not supposed to send an email, or email is blank/invalid, stop!
	if ( !$email_on || !is_email( $email ) )
		return;

	// Check for updates
	$update_check = genesis_update_check();

	// If no new version is available, stop!
	if ( !$update_check )
		return;

	// If we've already sent an email for this version, stop!
	if ( get_option('genesis-update-email') == $update_check['new_version'] )
		return;

	// Let's send an email!
	$subject = sprintf( __('Genesis %s is available for %s', 'genesis'), esc_html( $update_check['new_version'] ), home_url() );
	$message = sprintf( __('Genesis %s is now available. We have provided 1-click updates for this theme, so please log into your dashboard and update at your earliest convenience.', 'genesis'), esc_html( $update_check['new_version'] ) );
	$message .= "\n\n" . wp_login_url();

	// Update the option so we don't send emails on every pageload!
	update_option('genesis-update-email', $update_check['new_version'], TRUE);

	// send that puppy!
	wp_mail( sanitize_email($email), $subject, $message );

}

add_filter('site_transient_update_themes', 'genesis_update_push');
add_filter('transient_update_themes', 'genesis_update_push');
/**
 * This function filters the value that is returned when
 * WordPress tries to pull theme update transient data. It uses
 * genesis_update_check() to check to see if we need to do an
 * update, and if so, adds the proper array to the $value->response
 * object. WordPress handles the rest.
 *
 * @since 1.1
 */
function genesis_update_push($value) {

	$genesis_update = genesis_update_check();

	if ( $genesis_update ) {
		$value->response['genesis'] = $genesis_update;
	}

	return $value;

}

add_action('load-update.php', 'genesis_clear_update_transient');
add_action('load-themes.php', 'genesis_clear_update_transient');
/**
 * This function clears out the Genesis update transient data
 * so that the server will do a fresh version check when the
 * update is complete, or when the user loads certain admin pages.
 *
 * It also disables the update nag on those pages, as well.
 *
 * @since 1.1
 */
function genesis_clear_update_transient() {

	delete_transient('genesis-update');
	remove_action('admin_notices', 'genesis_update_nag');

}

/**
 * This function takes an array of keys passed by the user and converts them from
 * Genesis options into vestigial options, for backward compatibility.
 *
 * @since 1.6
 */
function _genesis_vestige( $keys = array(), $setting = GENESIS_SETTINGS_FIELD ) {

	/** If no $keys passed, do nothing */
	if ( ! $keys )
		return;

	/** Pull options */
	$options = get_option( $setting );
	$vestige = get_option( 'genesis-vestige' );

	/** Cycle through $keys, creating new vestige array */
	$new_vestige = array();
	foreach ( (array) $keys as $key ) {

		if ( isset( $options[$key] ) ) {
			$new_vestige[$key] = $options[$key];
			unset( $options[$key] );
		}

	}

	/** If no new vestigial options being pushed, do nothing */
	if ( ! $new_vestige )
		return;

	/** Merge the arrays, if necessary */
	$vestige = $vestige ? wp_parse_args( $new_vestige, $vestige ) : $new_vestige;

	/** Insert into options table */
	update_option( 'genesis-vestige', $vestige );
	update_option( $setting, $options );

}

/**
 * This function takes an array of new settings, merges them with the old settings,
 * and pushes them into the database via update_option().
 *
 * @since 1.7
 */
function _genesis_update_settings( $new = '', $setting = GENESIS_SETTINGS_FIELD ) {
	update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
}