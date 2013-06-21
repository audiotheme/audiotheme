<?php
// Determine where the WordPress Unit Tests are located.
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( empty( $wp_tests_dir ) ) {
	$config = json_decode( file_get_contents( dirname( dirname( __FILE__ ) ) . '/config.json' ), true );
	if ( ! empty( $config['phpunit']['wp-tests-dir'] ) ) {
		$wp_tests_dir = $config['phpunit']['wp-tests-dir'];
	}
}

require_once( $wp_tests_dir . '/includes/functions.php' );

function _manually_load_plugin() {
	require( dirname( dirname( __FILE__ ) ) . '/audiotheme.php' );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require( $wp_tests_dir . '/includes/bootstrap.php' );
