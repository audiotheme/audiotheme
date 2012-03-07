<?php
/**
 * Controls translation of Genesis.
 *
 * @package Genesis
 */

// used for theme localization
load_theme_textdomain('genesis', GENESIS_LANGUAGES_DIR);
$locale = get_locale();
$locale_file = GENESIS_LANGUAGES_DIR . "/$locale.php";
if ( is_readable( $locale_file ) )
	require_once( $locale_file );

/* Uncomment this to test your localization, make sure to enter the right language code.
add_filter('locale','test_localization');
function test_localization( $locale ) {
	return "nl_NL";
}
/**/