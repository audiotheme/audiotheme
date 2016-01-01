<?php

namespace AudioTheme\Test\Integration;

use AudioTheme_License;


class LicenseTest extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_save() {
		$key = 'abcdef';
		$license = new AudioTheme_License( $key );
		$license->save();

		$this->assertEquals( $key, get_option( 'audiotheme_license_key' ) );
	}
}
