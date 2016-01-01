<?php

namespace AudioTheme\Test\Unit;

use AudioTheme_License;


class LicenseTest extends \PHPUnit_Framework_TestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_has_class_constants() {
		$class = new \ReflectionClass( '\AudioTheme_License' );

		$this->assertArrayHasKey( 'OPTION_NAME', $class->getConstants() );
		$this->assertSame( 'audiotheme_license_key', AudioTheme_License::OPTION_NAME );
	}

	public function test_instantiation() {
		$license = new AudioTheme_License();
		$this->assertInstanceOf( '\AudioTheme_License', $license );
	}
}
